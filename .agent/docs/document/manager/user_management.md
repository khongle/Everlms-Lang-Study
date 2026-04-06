# Manager Role: User Management

The Manager role in EverLMS has been designed to allow targeted administration of users without granting full site administrative privileges.

## Permissions
Managers have the ability to:
- View the User Management dashboard (`/workspace/users`)
- Add new users (Students, Instructors) using the manager-specific creation form mode.
- Edit existing users (Students, Instructors) using the manager-specific edit form mode.
- Deactivate/Archive users.

**Security Constraints:**
Managers cannot view, edit, or delete accounts that have the `Administrator` role. This prevents privilege escalation.

## Form Modes
The user forms for Managers have been customized to remove excessive tabs and fields that are normally available to Administrators (such as Course Collections or Quiz tabs on the user profile).
- **Edit User**: `user.manager_edit` form mode is used.
- **Create User**: `user.manager_create` form mode is used.

These form modes are mapped to `Drupal\user\ProfileForm` and `Drupal\user\RegisterForm` respectively in `lms_course.module`.
