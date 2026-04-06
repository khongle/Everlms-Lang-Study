# Goal Description
Redesign the user profile page (`/user/{uid}`) to match the provided modern, dark-themed mockup. This involves changing the layout from a traditional left-sidebar structure to a top cover-photo header with a 2-column grid for details and statistics.

## Proposed Changes

### Theme Templates
#### [MODIFY] `user.html.twig`
- **Structure Redesign**: 
  - **Cover Photo Header**: Add a top section (`lms-profile__header`) featuring a dark geometric pattern background (placeholder for a cover photo).
  - **Avatar & Identity**: Center the circular avatar so it overlaps the bottom edge of the cover photo. Place the Full Name and Job Title directly underneath.
  - **Bottom Grid**: Create a 2-column layout (`lms-profile__body`).
    - **Left Column (Details)**: Move `field_bio` and contact information (Email, Location, Website, Social Links) here. Formatted cleanly as read-only fields.
    - **Right Column (Learning Statistics)**: Create a dedicated card for statistics containing "Courses Enrolled" and "Certificates". Display them dynamically based on the current template's available KPIs. Add "Member Since" to this card.

### Cascading Style Sheets
#### [MODIFY] `_profile.scss`
- Refactor the `.lms-profile` classes to implement the new layout using CSS Grid/Flexbox.
- Style `.lms-profile__cover` (height, background pattern, relative positioning).
- Update `.lms-profile__avatar-wrapper` to have absolute positioning to overlap the cover, with matching borders and glow effects from the mockup.
- Style the `.lms-profile__stats-grid` to look like the "Learning Statistics" card in the mockup.

## Verification Plan

### Automated Compilation
- I will execute `npm run build` in the `lms_theme` directory to compile `main.css`.

### Manual Verification
- I will prompt you to visit `http://everlms.ddev.site/user/3` to visually verify the profile layout improvements against your design.
