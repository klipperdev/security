<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Listener;

use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * PermissionExceptionListener display specific page when permission exception is throw.
 *
 * The onKernelException method must be connected to the kernel.exception event.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class PermissionExceptionListener
{
    /**
     * Method for a dependency injection.
     *
     * @param ExceptionEvent $event A event object
     */
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getException();

        if (!$exception instanceof AccessDeniedException) {
            return;
        }

        $event->setException(new AccessDeniedHttpException($exception->getMessage(), $exception));
    }
}
