# Site Development Notes

## Current Tasks

### ✅ Completed Tasks

- [x] I just noticed that you are applying the index page css as app.twig.. change this from app.twig to index.twig to match the page name...
- [x] Clean up Safa CSS to be purely a framework with only structure, utilities, and base functionality - removing all styling that should come from the active skin
- [x] Move all styling from Safa CSS to the Bismillah skin
- [x] Implement flexible layout architecture with app.twig as general layout and PAGENAME.twig for page-specific layouts
- [x] Update documentation to reflect the new layout architecture

### 🔄 In Progress Tasks

- [ ] Add more page-specific layouts as needed (settings.twig, dashboard.twig, etc.)
- [ ] Create additional skins beyond Bismillah
- [ ] Implement skin switching functionality
- [ ] Add mobile-specific layouts

### 📋 Future Tasks

- [ ] Implement A/B testing with different layouts
- [ ] Add skin configuration UI
- [ ] Create skin marketplace/gallery
- [ ] Implement real-time skin switching
- [ ] Add custom skin builder
- [ ] Implement skin import/export functionality

## Architecture Overview

### Current Layout System

```
layouts/
├── app.twig          # General layout (default) - uses skin system
├── index.twig        # Home page specific - direct CSS loading
├── auth.twig         # Authentication specific
└── [future].twig     # Page-specific layouts as needed
```

### CSS Loading Strategy

- **General Pages** (`app.twig`): Direct CSS loading `<link rel="stylesheet" href="/skins/Bismillah/css/bismillah.css">`
- **Page-Specific** (`index.twig`): Direct CSS loading `<link rel="stylesheet" href="/skins/Bismillah/css/bismillah.css">`

### Framework vs Skin Separation

- **Safa CSS**: Pure framework (utilities only)
- **Bismillah Skin**: All visual styling
- **Clear separation**: No styling conflicts between framework and skin

## Best Practices

1. **Use `app.twig` by default** for most pages
2. **Create page-specific layouts** only when custom styling is needed
3. **Keep layouts focused** - each layout should serve a specific purpose
4. **Maintain consistency** - page-specific layouts should still feel part of the application
5. **Document custom layouts** - explain why a custom layout was needed

## File Structure

```
resources/views/layouts/
├── app.twig          # General layout
├── index.twig        # Home page layout
├── auth.twig         # Auth pages layout
└── [future].twig     # Page-specific layouts

public/css/
└── safa.css          # Clean framework (utilities only)

skins/Bismillah/
└── css/
    └── bismillah.css     # All visual styling
```

---

## Tailwind

- [x] Tailwind CSS CDN to the layout so all Tailwind classes work properly
  
  - [x] Should we implement classes locally so that we dont have to call to the CDN?

---

- [x] user nav menu
  
  - [x] there should be a background behind the text if the user is on the current page; for example if the user is on the dashboard, a background should apply; if the user is on the profile page, the background should be on the profile link.

- [x] build profile page

---

## Github

- [x] Not getting commit contributions?

---

- [x] What did you do? When you created Iqra, you deleted all of the pages that we created including the dashboard, profile, settings. Bring them back

---
