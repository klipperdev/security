<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Tests\Validator\Constraints;

use Klipper\Component\Security\Identity\SubjectIdentity;
use Klipper\Component\Security\Sharing\SharingIdentityConfig;
use Klipper\Component\Security\Sharing\SharingManagerInterface;
use Klipper\Component\Security\Tests\Fixtures\Model\MockObject;
use Klipper\Component\Security\Tests\Fixtures\Model\MockPermission;
use Klipper\Component\Security\Tests\Fixtures\Model\MockRole;
use Klipper\Component\Security\Tests\Fixtures\Model\MockSharing;
use Klipper\Component\Security\Validator\Constraints\Sharing;
use Klipper\Component\Security\Validator\Constraints\SharingValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class SharingValidatorTest extends TestCase
{
    /**
     * @var MockObject|SharingManagerInterface
     */
    protected $sharingManager;

    /**
     * @var ExecutionContextInterface|MockObject
     */
    protected $context;

    protected ?SharingValidator $validator = null;

    protected function setUp(): void
    {
        $this->sharingManager = $this->getMockBuilder(SharingManagerInterface::class)->getMock();
        $this->context = $this->getMockBuilder(ExecutionContextInterface::class)->getMock();
        $this->validator = new SharingValidator($this->sharingManager);
    }

    public function testValidateWithEmptyFields(): void
    {
        $constraint = new Sharing();
        $sharing = new MockSharing();

        $this->addViolations([
            $this->createViolation('sharing.class.invalid', 'subjectClass', [
                '%class_property%' => 'subjectClass',
                '%class%' => null,
            ]),

            $this->createViolation('sharing.class.invalid', 'identityClass', [
                '%class_property%' => 'identityClass',
                '%class%' => null,
            ]),
        ]);

        $this->validator->initialize($this->context);
        $this->validator->validate($sharing, $constraint);
    }

    public function testValidateWithNotManagedClass(): void
    {
        $constraint = new Sharing();
        $sharing = new MockSharing();
        $sharing->setSubjectClass(MockObject::class);
        $sharing->setIdentityClass(MockRole::class);

        $this->sharingManager->expects(static::once())
            ->method('hasSharingVisibility')
            ->with(SubjectIdentity::fromClassname(MockObject::class))
            ->willReturn(false)
        ;

        $this->sharingManager->expects(static::once())
            ->method('hasIdentityConfig')
            ->with(MockRole::class)
            ->willReturn(false)
        ;

        $this->addViolations([
            $this->createViolation('sharing.class.not_managed', 'subjectClass', [
                '%class_property%' => 'subjectClass',
                '%class%' => MockObject::class,
            ]),

            $this->createViolation('sharing.class.not_managed', 'identityClass', [
                '%class_property%' => 'identityClass',
                '%class%' => MockRole::class,
            ]),
        ]);

        $this->validator->initialize($this->context);
        $this->validator->validate($sharing, $constraint);
    }

    public function testValidateFieldWithInvalidRole(): void
    {
        $constraint = new Sharing();
        $sharing = new MockSharing();
        $sharing->setSubjectClass(MockObject::class);
        $sharing->setIdentityClass(MockRole::class);
        $sharing->setRoles(['ROLE_TEST']);

        $this->sharingManager->expects(static::once())
            ->method('hasSharingVisibility')
            ->with(SubjectIdentity::fromClassname(MockObject::class))
            ->willReturn(true)
        ;

        $this->sharingManager->expects(static::once())
            ->method('hasIdentityConfig')
            ->with(MockRole::class)
            ->willReturn(true)
        ;

        $config = new SharingIdentityConfig(MockRole::class);

        $this->sharingManager->expects(static::once())
            ->method('getIdentityConfig')
            ->with(MockRole::class)
            ->willReturn($config)
        ;

        $this->addViolations([
            $this->createViolation('sharing.class.identity_not_roleable', 'roles', [
                '%class_property%' => 'identityClass',
                '%class%' => MockRole::class,
            ]),
        ]);

        $this->validator->initialize($this->context);
        $this->validator->validate($sharing, $constraint);
    }

    public function testValidateFieldWithInvalidPermission(): void
    {
        $constraint = new Sharing();
        $sharing = new MockSharing();
        $sharing->setSubjectClass(MockObject::class);
        $sharing->setIdentityClass(MockRole::class);
        $sharing->getPermissions()->add(new MockPermission());

        $this->sharingManager->expects(static::once())
            ->method('hasSharingVisibility')
            ->with(SubjectIdentity::fromClassname(MockObject::class))
            ->willReturn(true)
        ;

        $this->sharingManager->expects(static::once())
            ->method('hasIdentityConfig')
            ->with(MockRole::class)
            ->willReturn(true)
        ;

        $config = new SharingIdentityConfig(MockRole::class);

        $this->sharingManager->expects(static::once())
            ->method('getIdentityConfig')
            ->with(MockRole::class)
            ->willReturn($config)
        ;

        $this->addViolations([
            $this->createViolation('sharing.class.identity_not_permissible', 'permissions', [
                '%class_property%' => 'identityClass',
                '%class%' => MockRole::class,
            ]),
        ]);

        $this->validator->initialize($this->context);
        $this->validator->validate($sharing, $constraint);
    }

    public function testValidate(): void
    {
        $constraint = new Sharing();
        $sharing = new MockSharing();
        $sharing->setSubjectClass(MockObject::class);
        $sharing->setIdentityClass(MockRole::class);
        $sharing->setRoles(['ROLE_TEST']);
        $sharing->getPermissions()->add(new MockPermission());

        $this->sharingManager->expects(static::once())
            ->method('hasSharingVisibility')
            ->with(SubjectIdentity::fromClassname(MockObject::class))
            ->willReturn(true)
        ;

        $this->sharingManager->expects(static::once())
            ->method('hasIdentityConfig')
            ->with(MockRole::class)
            ->willReturn(true)
        ;

        $config = new SharingIdentityConfig(MockRole::class, 'role', true, true);

        $this->sharingManager->expects(static::once())
            ->method('getIdentityConfig')
            ->with(MockRole::class)
            ->willReturn($config)
        ;

        $this->validator->initialize($this->context);
        $this->validator->validate($sharing, $constraint);
    }

    /**
     * Create violation for return map.
     *
     * @param string $message    The message
     * @param string $path       The property path
     * @param array  $parameters The violation parameters
     *
     * @return array The return value map for the context
     */
    protected function createViolation(string $message, string $path, array $parameters = []): array
    {
        $vb = $this->getMockBuilder(ConstraintViolationBuilderInterface::class)->getMock();

        $vb->expects(static::once())
            ->method('atPath')
            ->with($path)
            ->willReturn($vb)
        ;

        $parametersMap = [];

        foreach ($parameters as $key => $value) {
            $parametersMap[] = [(string) $key, (string) $value, $vb];
        }

        $vb->expects(static::exactly(\count($parameters)))
            ->method('setParameter')
            ->willReturnMap($parametersMap)
        ;

        $vb->expects(static::once())
            ->method('addViolation')
        ;

        return [$message, [], $vb];
    }

    private function addViolations(array $violations): void
    {
        $violationsPos = 0;
        $this->context->expects(static::exactly(\count($violations)))
            ->method('buildViolation')
            ->willReturnCallback(function ($message, $params) use ($violations, &$violationsPos) {
                [$expectedMessage, $expectedParams, $returnValue] = $violations[$violationsPos];

                static::assertSame($expectedMessage, $message);
                static::assertEquals($expectedParams, $params);

                ++$violationsPos;

                return $returnValue;
            })
        ;
    }
}
