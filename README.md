Klipper Security Component
==========================

The Security component is is a Extended Role-Based Access Control (E-RBAC) including the management of roles,
role hierarchy, groups, and permissions with a granularity ranging from global permission to permission for
each field of each object. With the sharing rules, it's possible to define users, groups, roles or permissions
for each record of an object. In this way, a user can get more permissions due to the context defined by the
sharing rule.

Features include:

- Compatible with Symfony Security and user manager library (ex. [Friends Of Symfony User Bundle](https://github.com/FriendsOfSymfony/FOSUserBundle))
- Compatible with [Doctrine extensions](https://github.com/Atlantic18/DoctrineExtensions)
- Define the roles with hierarchy in Doctrine
- Define the groups with her roles in Doctrine
- Define the user with her roles and groups in Doctrine
- Define the organization with her roles in Doctrine (optional)
- Define the organization user with her roles and groups in Doctrine (optional)
- Defined the permissions on the roles in Doctrine
- Defined the permissions on the sharing entry in Doctrine
- Defined the permissions in the configuration (with global config permissions in Doctrine)
- Defined the roles on the sharing entry in Doctrine
- Share each records by user, role, groups or organization and defined her permissions and roles
- Merge the permissions of roles children of associated roles with user, role, group, organization, sharing entry, and token
- Security Identity Manager to retrieving security identities from tokens (current user,
  all roles, all groups and organization)
- AuthorizationChecker to check the permissions for objects
- Permission Manager to retrieve the permissions with her operations (with a cache for the configuration)
- Sharing Manager to retrieve the sharing entry with her permissions and roles (with a cache for the configuration)
- Symfony validators of permission and sharing model
- Permission Voter to use the Symfony Authorization Checker
- Define a role for various host with direct injection in token (regex compatible)
- Execution cache system and PSR-6 Caching Implementation for the permissions getter
- Execution cache and PSR-6 Caching Implementation for the determination of all roles in
  hierarchy (with user, group, role, organization, organization user, token)
- Doctrine ORM Filter to filtering the records in query defined by the sharing rules (compatible with doctrine caches)
- Doctrine Listener to empty the record field value for all query type
- Doctrine Listener to keep the old value in the record field value if the user has not the permission of action
- Organization with users and roles
- Authorization expression voter with injectable custom variables (to build custom expression functions with dependencies)
- `is_basic_auth` expression language function
- `is_granted` expression language function
- `@Permission` and `@PermissionField` annotations to configure the global permissions directly in the classes
- `@SharingSubject` and `@SharingIdentity` annotations to configure the global sharing directly in the classes

Resources
---------

- [Documentation](https://doc.klipper.dev/components/security)
- [Report issues](https://github.com/klipperdev/klipper/issues)
  and [send Pull Requests](https://github.com/klipperdev/klipper/pulls)
  in the [main Klipper repository](https://github.com/klipperdev/klipper)
