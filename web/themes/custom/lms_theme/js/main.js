/**
 * @file
 * EverLMS Theme main JavaScript.
 */

(function ($, Drupal, once) {
    'use strict';

    /**
     * Mobile navbar toggle.
     */
    Drupal.behaviors.lmsNavbar = {
        attach: function (context) {
            once('lms-navbar-toggle', '.lms-navbar__toggle', context).forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var nav = document.querySelector('.lms-navbar__nav');
                    if (nav) {
                        var isOpen = nav.classList.toggle('lms-navbar__nav--open');
                        this.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                    }
                });
            });
        }
    };

    /**
     * The header's profile mega-menu (avatar + name -> Dashboard/My
     * Courses/.../My Profile) only ever reveals on CSS :hover
     * (scss/components/_mega-menu.scss) — fine with a mouse, but a
     * touchscreen has no hover state, so tapping the trigger (a real
     * <a href> pointing at My Profile) just navigated straight there
     * instead of ever showing the dropdown. This adds a tap-to-open
     * fallback (an `.is-open` class the same SCSS rule also reveals on)
     * for exactly that case — matchMedia('(hover: hover)') is false on
     * essentially every touch device and true on anything with a real
     * mouse, so desktop hover behavior is untouched either way.
     */
    Drupal.behaviors.lmsProfileMegaMenu = {
        attach: function (context) {
            once('lms-profile-mega-menu', '.lms-mega-menu-wrapper--profile', context).forEach(function (wrapper) {
                var trigger = wrapper.querySelector('.lms-navbar__account-trigger');
                var dropdown = wrapper.querySelector('.lms-mega-dropdown');
                if (!trigger || !dropdown) {
                    return;
                }

                trigger.addEventListener('click', function (event) {
                    if (window.matchMedia && window.matchMedia('(hover: hover)').matches) {
                        // Has a real pointer — :hover already handles this normally.
                        return;
                    }
                    // Touch/mobile: the trigger is a menu toggle only, never a
                    // navigation link. Every tap opens/closes the dropdown;
                    // actual navigation happens through the "My Profile" link
                    // inside it.
                    event.preventDefault();
                    wrapper.classList.toggle('is-open');
                });

                document.addEventListener('click', function (event) {
                    if (wrapper.classList.contains('is-open') && !wrapper.contains(event.target)) {
                        wrapper.classList.remove('is-open');
                    }
                });
            });
        }
    };

    /**
     * Smooth scroll for anchor links.
     */
    Drupal.behaviors.lmsSmoothScroll = {
        attach: function (context) {
            once('lms-smooth-scroll', 'a[href^="#"]:not(.lms-tabs__link)', context).forEach(function (link) {
                link.addEventListener('click', function (e) {
                    var targetId = this.getAttribute('href');
                    if (targetId && targetId.length > 1) {
                        var target = document.querySelector(targetId);
                        if (target) {
                            e.preventDefault();
                            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        }
                    }
                });
            });
        }
    };

    /**
     * Course Detail Tabs.
     */
    Drupal.behaviors.lmsTabs = {
        attach: function (context) {
            once('lms-tabs', '.lms-tabs', context).forEach(function (tabContainer) {
                const links = tabContainer.querySelectorAll('.lms-tabs__link');
                const panelMap = new Map();
                links.forEach(function (link) {
                    const hash = link.getAttribute('href');
                    if (!hash || hash.charAt(0) !== '#') {
                        return;
                    }

                    const panel = document.querySelector(hash);
                    if (panel) {
                        panelMap.set(hash, panel);
                    }
                });

                const panels = Array.from(panelMap.values());
                if (!links.length || !panels.length) {
                    return;
                }

                const activeSeed = tabContainer.querySelector('.lms-tabs__link--active') || links[0];
                const inactiveSeed = Array.from(links).find(link => !link.classList.contains('lms-tabs__link--active')) || activeSeed;
                const activeSeedStyles = activeSeed ? window.getComputedStyle(activeSeed) : null;
                const inactiveSeedStyles = inactiveSeed ? window.getComputedStyle(inactiveSeed) : activeSeedStyles;
                const tabStateStyles = {
                    activeColor: activeSeedStyles ? activeSeedStyles.color : '',
                    activeBorderColor: activeSeedStyles ? activeSeedStyles.borderBottomColor : '',
                    activeBorderWidth: activeSeedStyles ? activeSeedStyles.borderBottomWidth : '2px',
                    activeFontWeight: activeSeedStyles ? activeSeedStyles.fontWeight : '600',
                    inactiveColor: inactiveSeedStyles ? inactiveSeedStyles.color : '',
                    inactiveFontWeight: inactiveSeedStyles ? inactiveSeedStyles.fontWeight : '500'
                };

                function syncTabLinkStyles(activeLink) {
                    links.forEach(function (link) {
                        const isActive = link === activeLink;
                        link.style.color = isActive ? tabStateStyles.activeColor : tabStateStyles.inactiveColor;
                        link.style.borderBottomStyle = 'solid';
                        link.style.borderBottomWidth = tabStateStyles.activeBorderWidth;
                        link.style.borderBottomColor = isActive ? tabStateStyles.activeBorderColor : 'transparent';
                        link.style.fontWeight = isActive ? tabStateStyles.activeFontWeight : tabStateStyles.inactiveFontWeight;
                    });
                }

                function activateTab(hash) {
                    if (!hash) return false;
                    
                    const targetLink = tabContainer.querySelector(`a[href="${hash}"]`);
                    const targetPanel = panelMap.get(hash);
                    
                    if (targetLink && targetPanel) {
                        links.forEach(function (link) {
                            const isActive = link === targetLink;
                            link.classList.toggle('lms-tabs__link--active', isActive);
                            link.setAttribute('aria-selected', isActive ? 'true' : 'false');
                        });
                        panels.forEach(function (panel) {
                            panel.style.display = 'none';
                            panel.setAttribute('aria-hidden', 'true');
                        });
                        
                        syncTabLinkStyles(targetLink);
                        targetPanel.style.display = 'block';
                        targetPanel.setAttribute('aria-hidden', 'false');
                        targetPanel.style.animation = 'fadeIn 0.3s ease';
                        return true;
                    }
                    return false;
                }

                // Initial activation based on URL hash - Delayed to ensure DOM is ready
                setTimeout(() => {
                    if (window.location.hash) {
                        if (!activateTab(window.location.hash)) {
                            activateTab(links[0].getAttribute('href'));
                        }
                    } else if (links.length > 0) {
                        // Default to first tab if no hash
                        activateTab(links[0].getAttribute('href'));
                    }
                }, 100);

                links.forEach(link => {
                    link.addEventListener('click', function (e) {
                        e.preventDefault();
                        const hash = this.getAttribute('href');
                        activateTab(hash);
                        // Update URL without jumping
                        history.pushState(null, null, hash);
                    });
                });
            });
        }
    };

    /**
     * Learning progress bars (dashboard + my-courses).
     */
    Drupal.behaviors.lmsLearningProgress = {
        attach: function (context) {
            once('lms-learning-progress', '.lms-learning-progress__fill[data-progress]', context).forEach(function (fill) {
                var raw = parseFloat(fill.getAttribute('data-progress'));
                var progress = Number.isFinite(raw) ? Math.max(0, Math.min(100, raw)) : 0;
                fill.style.setProperty('--progress', progress + '%');
            });

            once('lms-lesson-progress', '.lms-progress-bar__fill[data-progress]', context).forEach(function (fill) {
                var raw = parseFloat(fill.getAttribute('data-progress'));
                var progress = Number.isFinite(raw) ? Math.max(0, Math.min(100, raw)) : 0;
                fill.style.width = progress + '%';
            });

            once('lms-home-progress', '.lms-progress-fill[data-progress]', context).forEach(function (fill) {
                var raw = parseFloat(fill.getAttribute('data-progress'));
                var progress = Number.isFinite(raw) ? Math.max(0, Math.min(100, raw)) : 0;
                fill.style.width = progress + '%';
            });
        }
    };

    /**
     * Homepage FAQ toggles for custom skins using non-Bootstrap accordions.
     */
    Drupal.behaviors.lmsHomepageFaq = {
        attach: function (context) {
            once('lms-homepage-faq', '.layout-playful .faq-question, .layout-corporate .faq-question', context).forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var item = btn.closest('.faq-item');
                    var scope = btn.closest('.layout-playful, .layout-corporate');

                    if (!item || !scope) {
                        return;
                    }

                    var isActive = item.classList.contains('active');
                    scope.querySelectorAll('.faq-item').forEach(function (el) {
                        el.classList.remove('active');
                    });

                    if (!isActive) {
                        item.classList.add('active');
                    }
                });
            });
        }
    };

    /**
     * Lesson view interactions (mark complete, notes, tabs, sidebar, SCORM API shim).
     */
    Drupal.behaviors.lmsLessonView = {
        attach: function (context) {
            once('lms-lesson-view', '.lms-learn[data-course-id][data-lesson-id]', context).forEach(function (container) {
                var courseId = container.getAttribute('data-course-id');
                var lessonId = container.getAttribute('data-lesson-id');
                var lessonType = container.getAttribute('data-lesson-type') || '';
                var myCoursesUrl = container.getAttribute('data-my-courses-url') || '/my-courses';

                if (lessonType === 'scorm') {
                    if (typeof window.API === 'undefined') {
                        window.API = {
                            LMSInitialize: function () { return 'true'; },
                            LMSFinish: function () { return 'true'; },
                            LMSGetValue: function () { return ''; },
                            LMSSetValue: function () { return 'true'; },
                            LMSCommit: function () { return 'true'; },
                            LMSGetLastError: function () { return '0'; },
                            LMSGetErrorString: function () { return 'No error'; },
                            LMSGetDiagnostic: function () { return 'No error'; }
                        };
                    }
                    if (typeof window.API_1484_11 === 'undefined') {
                        window.API_1484_11 = {
                            Initialize: function () { return 'true'; },
                            Terminate: function () { return 'true'; },
                            GetValue: function () { return ''; },
                            SetValue: function () { return 'true'; },
                            Commit: function () { return 'true'; },
                            GetLastError: function () { return '0'; },
                            GetErrorString: function () { return 'No error'; },
                            GetDiagnostic: function () { return 'No error'; }
                        };
                    }
                }

                var markCompleteBtn = container.querySelector('#lms-mark-complete');
                if (markCompleteBtn) {
                    markCompleteBtn.addEventListener('click', async function () {
                        var originalText = markCompleteBtn.innerHTML;
                        markCompleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ' + Drupal.t('Processing...');
                        markCompleteBtn.disabled = true;

                        try {
                            var response = await fetch('/api/lms/progress/mark-complete', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({
                                    course_id: courseId,
                                    lesson_id: lessonId
                                })
                            });

                            var data = {};
                            try {
                                data = await response.json();
                            } catch (jsonErr) {
                                data = {};
                            }

                            if (response.ok && data.success) {
                                markCompleteBtn.innerHTML = '<i class="fas fa-check-double"></i> ' + Drupal.t('Completed!');
                                markCompleteBtn.classList.add('is-completed');

                                if (data.next_lesson_url) {
                                    setTimeout(function () {
                                        window.location.href = data.next_lesson_url;
                                    }, 1000);
                                } else if (data.course_completed) {
                                    setTimeout(function () {
                                        window.alert(Drupal.t('Congratulations! You have completed the course!'));
                                        window.location.href = myCoursesUrl;
                                    }, 1000);
                                }
                                return;
                            }

                            var errorMsg = data && data.error ? data.error : Drupal.t('Unable to complete this lesson.');
                            markCompleteBtn.innerHTML = originalText;
                            markCompleteBtn.disabled = false;
                            window.alert(Drupal.t('Error: @message', { '@message': errorMsg }));
                        } catch (err) {
                            markCompleteBtn.innerHTML = originalText;
                            markCompleteBtn.disabled = false;
                            console.error(err);
                        }
                    });
                }

                var noteTextarea = container.querySelector('#lms-note-textarea');
                var noteSaveBtn = container.querySelector('#lms-note-save-btn');
                var noteDeleteBtn = container.querySelector('#lms-note-delete-btn');
                var noteStatus = container.querySelector('#lms-note-status');
                var noteAttachmentList = container.querySelector('#lms-note-attachment-list');
                var noteAttachmentInput = container.querySelector('#lms-note-attachment-input');

                if (noteTextarea && noteSaveBtn && noteDeleteBtn && noteStatus) {
                    var originalNoteText = '';
                    var hasUnsavedChanges = false;

                    var setDeleteVisible = function (visible) {
                        noteDeleteBtn.classList.toggle('is-visible', visible);
                    };

                    var setSaveEnabled = function (enabled) {
                        noteSaveBtn.disabled = !enabled;
                    };

                    var showStatus = function (html, tone) {
                        noteStatus.innerHTML = html;
                        noteStatus.classList.remove('is-muted', 'is-success', 'is-error');
                        noteStatus.classList.add('is-visible');
                        if (tone) {
                            noteStatus.classList.add('is-' + tone);
                        }
                    };

                    var hideStatus = function () {
                        noteStatus.classList.remove('is-visible', 'is-muted', 'is-success', 'is-error');
                    };

                    var attachmentIcon = function (mime) {
                        if (mime && mime.indexOf('audio') === 0) return 'fa-file-audio';
                        if (mime && mime.indexOf('image') === 0) return 'fa-file-image';
                        if (mime === 'application/pdf') return 'fa-file-pdf';
                        return 'fa-file';
                    };

                    var formatFileSize = function (bytes) {
                        if (!bytes) return '0 KB';
                        var kb = bytes / 1024;
                        if (kb < 1024) return Math.round(kb) + ' KB';
                        return (kb / 1024).toFixed(1) + ' MB';
                    };

                    var renderAttachments = function (attachments) {
                        if (!noteAttachmentList) return;
                        noteAttachmentList.innerHTML = '';
                        (attachments || []).forEach(function (att) {
                            var li = document.createElement('li');
                            li.className = 'lms-learn__notes-attachment-item';
                            li.setAttribute('data-file-id', att.file_id);
                            li.innerHTML =
                                '<i class="fas ' + attachmentIcon(att.mime) + ' lms-learn__notes-attachment-icon"></i>' +
                                '<a href="' + att.url + '" target="_blank" rel="noopener noreferrer" class="lms-learn__notes-attachment-name">' + att.filename + '</a>' +
                                '<span class="lms-learn__notes-attachment-size">' + formatFileSize(att.size) + '</span>' +
                                '<button type="button" class="lms-learn__notes-attachment-remove" title="' + Drupal.t('Remove attachment') + '"><i class="fas fa-times"></i></button>';
                            noteAttachmentList.appendChild(li);
                        });
                    };

                    if (noteAttachmentList) {
                        noteAttachmentList.addEventListener('click', async function (e) {
                            var removeBtn = e.target.closest('.lms-learn__notes-attachment-remove');
                            if (!removeBtn) return;

                            var li = removeBtn.closest('.lms-learn__notes-attachment-item');
                            var fileId = li ? li.getAttribute('data-file-id') : null;
                            if (!fileId) return;

                            if (!window.confirm(Drupal.t('Remove this attachment?'))) return;

                            removeBtn.disabled = true;
                            try {
                                var response = await fetch('/api/lms/note/remove-attachment', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'Accept': 'application/json'
                                    },
                                    body: JSON.stringify({ lesson_id: lessonId, file_id: fileId })
                                });

                                if (response.ok) {
                                    li.remove();
                                    setDeleteVisible(true);
                                } else {
                                    showStatus('<i class="fas fa-exclamation-triangle lms-note-status-icon is-error"></i> ' + Drupal.t('Error removing attachment'), 'error');
                                    removeBtn.disabled = false;
                                }
                            } catch (err) {
                                showStatus('<i class="fas fa-exclamation-triangle lms-note-status-icon is-error"></i> ' + Drupal.t('Network error'), 'error');
                                removeBtn.disabled = false;
                                console.error(err);
                            }
                        });
                    }

                    if (noteAttachmentInput) {
                        noteAttachmentInput.addEventListener('change', async function () {
                            var files = Array.prototype.slice.call(noteAttachmentInput.files || []);
                            noteAttachmentInput.value = '';
                            if (!files.length) return;

                            var allowedExt = ['mp3', 'wav', 'pdf', 'jpg', 'jpeg', 'png', 'gif'];
                            var maxSize = 10 * 1024 * 1024;

                            for (var i = 0; i < files.length; i++) {
                                var file = files[i];
                                var ext = (file.name.split('.').pop() || '').toLowerCase();

                                if (allowedExt.indexOf(ext) === -1) {
                                    showStatus('<i class="fas fa-exclamation-triangle lms-note-status-icon is-error"></i> ' + Drupal.t('File type not allowed: @name', { '@name': file.name }), 'error');
                                    continue;
                                }
                                if (file.size > maxSize) {
                                    showStatus('<i class="fas fa-exclamation-triangle lms-note-status-icon is-error"></i> ' + Drupal.t('File too large (max 10 MB): @name', { '@name': file.name }), 'error');
                                    continue;
                                }

                                showStatus('<i class="fas fa-spinner fa-spin"></i> ' + Drupal.t('Uploading @name...', { '@name': file.name }), 'muted');

                                try {
                                    var formData = new FormData();
                                    formData.append('file', file);
                                    formData.append('lesson_id', lessonId);
                                    formData.append('course_id', courseId);

                                    var response = await fetch('/api/lms/note/upload-attachment', {
                                        method: 'POST',
                                        headers: { 'Accept': 'application/json' },
                                        body: formData
                                    });
                                    var result = await response.json();

                                    if (response.ok && result.status === 'uploaded') {
                                        var li = document.createElement('li');
                                        li.className = 'lms-learn__notes-attachment-item';
                                        li.setAttribute('data-file-id', result.file_id);
                                        li.innerHTML =
                                            '<i class="fas ' + attachmentIcon(result.mime) + ' lms-learn__notes-attachment-icon"></i>' +
                                            '<a href="' + result.url + '" target="_blank" rel="noopener noreferrer" class="lms-learn__notes-attachment-name">' + result.filename + '</a>' +
                                            '<span class="lms-learn__notes-attachment-size">' + formatFileSize(result.size) + '</span>' +
                                            '<button type="button" class="lms-learn__notes-attachment-remove" title="' + Drupal.t('Remove attachment') + '"><i class="fas fa-times"></i></button>';
                                        if (noteAttachmentList) noteAttachmentList.appendChild(li);

                                        setDeleteVisible(true);
                                        showStatus('<i class="fas fa-check-circle lms-note-status-icon is-success"></i> ' + Drupal.t('Attached @name', { '@name': file.name }), 'success');
                                        setTimeout(hideStatus, 2000);
                                    } else {
                                        showStatus('<i class="fas fa-exclamation-triangle lms-note-status-icon is-error"></i> ' + (result.error || Drupal.t('Error uploading file')), 'error');
                                    }
                                } catch (err) {
                                    showStatus('<i class="fas fa-exclamation-triangle lms-note-status-icon is-error"></i> ' + Drupal.t('Network error'), 'error');
                                    console.error(err);
                                }
                            }
                        });
                    }

                    window.addEventListener('beforeunload', function (e) {
                        if (hasUnsavedChanges) {
                            e.preventDefault();
                            e.returnValue = '';
                        }
                    });

                    noteTextarea.addEventListener('input', function () {
                        hasUnsavedChanges = noteTextarea.value !== originalNoteText;
                        setSaveEnabled(hasUnsavedChanges);
                    });

                    fetch('/api/lms/note/get?lesson_id=' + encodeURIComponent(lessonId))
                        .then(function (r) { return r.json(); })
                        .then(function (data) {
                            noteTextarea.disabled = false;
                            noteTextarea.placeholder = Drupal.t('Type your notes here...');
                            if (noteAttachmentInput) noteAttachmentInput.disabled = false;

                            if (data && data.status === 'found') {
                                noteTextarea.value = data.note_text || '';
                                originalNoteText = noteTextarea.value;
                                setDeleteVisible(true);
                            } else {
                                originalNoteText = '';
                                setDeleteVisible(false);
                            }
                            renderAttachments(data && data.attachments);

                            hasUnsavedChanges = false;
                            setSaveEnabled(false);
                        })
                        .catch(function () {
                            noteTextarea.disabled = false;
                            noteTextarea.placeholder = Drupal.t('Type your notes here...');
                            if (noteAttachmentInput) noteAttachmentInput.disabled = false;
                            setDeleteVisible(false);
                            setSaveEnabled(false);
                        });

                    noteSaveBtn.addEventListener('click', async function () {
                        showStatus('<i class="fas fa-spinner fa-spin"></i> ' + Drupal.t('Saving...'), 'muted');
                        setSaveEnabled(false);

                        try {
                            var response = await fetch('/api/lms/note/save', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({
                                    course_id: courseId,
                                    lesson_id: lessonId,
                                    note_text: noteTextarea.value,
                                    video_timestamp: 0
                                })
                            });

                            if (response.ok) {
                                showStatus('<i class="fas fa-check-circle lms-note-status-icon is-success"></i> ' + Drupal.t('Saved successfully!'), 'success');
                                setDeleteVisible(true);

                                originalNoteText = noteTextarea.value;
                                hasUnsavedChanges = false;
                                setSaveEnabled(false);

                                setTimeout(hideStatus, 2000);
                            } else {
                                showStatus('<i class="fas fa-exclamation-triangle lms-note-status-icon is-error"></i> ' + Drupal.t('Error saving'), 'error');
                                setSaveEnabled(true);
                            }
                        } catch (err) {
                            showStatus('<i class="fas fa-exclamation-triangle lms-note-status-icon is-error"></i> ' + Drupal.t('Network error'), 'error');
                            setSaveEnabled(true);
                            console.error(err);
                        }
                    });

                    noteDeleteBtn.addEventListener('click', async function () {
                        if (!window.confirm(Drupal.t('Are you sure you want to delete this note?'))) {
                            return;
                        }

                        noteDeleteBtn.disabled = true;
                        showStatus('<i class="fas fa-spinner fa-spin"></i> ' + Drupal.t('Deleting...'), 'muted');

                        try {
                            var response = await fetch('/api/lms/note/delete', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({
                                    lesson_id: lessonId
                                })
                            });

                            if (response.ok) {
                                noteTextarea.value = '';
                                originalNoteText = '';
                                hasUnsavedChanges = false;

                                setDeleteVisible(false);
                                setSaveEnabled(false);
                                renderAttachments([]);
                                showStatus('<i class="fas fa-trash-alt lms-note-status-icon is-error"></i> ' + Drupal.t('Deleted successfully!'), 'success');
                                setTimeout(hideStatus, 2000);
                            } else {
                                showStatus('<i class="fas fa-exclamation-triangle lms-note-status-icon is-error"></i> ' + Drupal.t('Error deleting'), 'error');
                            }
                        } catch (err) {
                            showStatus('<i class="fas fa-exclamation-triangle lms-note-status-icon is-error"></i> ' + Drupal.t('Network error'), 'error');
                            console.error(err);
                        } finally {
                            noteDeleteBtn.disabled = false;
                        }
                    });
                }

                var tabItems = container.querySelectorAll('.lms-tab-item');
                var tabPanes = container.querySelectorAll('.lms-tab-pane');
                if (tabItems.length && tabPanes.length) {
                    var activateLessonTab = function (tabName) {
                        if (!tabName) {
                            return;
                        }

                        tabItems.forEach(function (item) {
                            var isActive = item.getAttribute('data-tab') === tabName;
                            item.classList.toggle('active', isActive);
                            item.setAttribute('aria-selected', isActive ? 'true' : 'false');
                        });

                        tabPanes.forEach(function (pane) {
                            pane.classList.remove('is-active');
                            pane.setAttribute('aria-hidden', 'true');
                        });

                        var targetPane = container.querySelector('#tab-' + tabName);
                        if (targetPane) {
                            targetPane.classList.add('is-active');
                            targetPane.setAttribute('aria-hidden', 'false');
                        }
                    };

                    tabItems.forEach(function (tab) {
                        tab.addEventListener('click', function () {
                            activateLessonTab(tab.getAttribute('data-tab'));
                        });
                    });

                    var currentTab = container.querySelector('.lms-tab-item.active');
                    var initialTab = currentTab ? currentTab.getAttribute('data-tab') : null;
                    if (!initialTab && tabItems[0]) {
                        initialTab = tabItems[0].getAttribute('data-tab');
                    }
                    activateLessonTab(initialTab);
                }

                var sidebarToggleBtn = container.querySelector('#lms-sidebar-toggle');
                var floatingToggleBtn = container.querySelector('#lms-floating-sidebar-toggle');
                var readSidebarCollapsed = function () {
                    try {
                        return window.localStorage.getItem('lms_sidebar_collapsed') === 'true';
                    } catch (e) {
                        return false;
                    }
                };
                var writeSidebarCollapsed = function (value) {
                    try {
                        window.localStorage.setItem('lms_sidebar_collapsed', value ? 'true' : 'false');
                    } catch (e) {
                        // Ignore localStorage access errors in restricted environments.
                    }
                };
                var applySidebarIcons = function (isCollapsed) {
                    if (sidebarToggleBtn) {
                        var topIcon = sidebarToggleBtn.querySelector('i');
                        if (topIcon) {
                            topIcon.classList.toggle('fa-expand-arrows-alt', !isCollapsed);
                            topIcon.classList.toggle('fa-compress-arrows-alt', isCollapsed);
                        }
                    }

                    if (floatingToggleBtn) {
                        var floatIcon = floatingToggleBtn.querySelector('i');
                        if (floatIcon) {
                            floatIcon.classList.toggle('fa-chevron-right', !isCollapsed);
                            floatIcon.classList.toggle('fa-chevron-left', isCollapsed);
                        }
                    }
                };

                var toggleSidebar = function () {
                    container.classList.toggle('lms-learn--sidebar-collapsed');
                    var isCollapsed = container.classList.contains('lms-learn--sidebar-collapsed');
                    writeSidebarCollapsed(isCollapsed);
                    applySidebarIcons(isCollapsed);
                };

                var savedCollapsed = readSidebarCollapsed();
                if (savedCollapsed) {
                    container.classList.add('lms-learn--sidebar-collapsed');
                }
                applySidebarIcons(savedCollapsed);

                if (sidebarToggleBtn) {
                    sidebarToggleBtn.addEventListener('click', toggleSidebar);
                }
                if (floatingToggleBtn) {
                    floatingToggleBtn.addEventListener('click', toggleSidebar);
                }
            });
        }
    };

    /**
     * Course Catalog Filtering with AJAX.
     */
    Drupal.behaviors.lmsCatalogFilter = {
        attach: function(context) {
            once('lms-catalog-filter', '.lms-catalog', context).forEach(function(catalog) {
                const searchInput = $(catalog).find('#course-search');
                const categorySelect = $(catalog).find('#category-filter');
                const sortSelect = $(catalog).find('#sort-courses');
                const mainArea = $(catalog).find('.lms-catalog__main');
                const coursesGrid = $(catalog).find('.grid-courses');
                let timeoutId = null;

                function renderStars(ratingStr) {
                    const rating = parseFloat(ratingStr) || 5.0;
                    const floor = Math.floor(rating);
                    const hasHalf = (rating - floor) >= 0.5;
                    const empty = 5 - floor - (hasHalf ? 1 : 0);
                    let html = '';
                    if (floor > 0) {
                        for (let i = 0; i < floor; i++) html += '<i class="fas fa-star"></i>';
                    }
                    if (hasHalf) {
                        html += '<i class="fas fa-star-half-alt"></i>';
                    }
                    if (empty > 0) {
                        for (let i = 0; i < empty; i++) html += '<i class="far fa-star"></i>';
                    }
                    return html + `<span style="color: #a1a1aa; margin-left: 4px;">${rating.toFixed(1)}</span>`;
                }

                function renderCourseCard(card) {
                    const targetLink = (card.is_enrolled && card.continue_url) ? card.continue_url : card.url;
                    
                    let badgesHtml = `<div style="position: absolute; top: 0.5rem; left: 0.5rem; display: flex; flex-direction: column; gap: 0.5rem; z-index: 10; align-items: flex-start;">`;
                    if (card.category) {
                        badgesHtml += `<span class="card-lms__category" style="position: static; margin: 0;">${card.category}</span>`;
                    }
                    if (card.bestseller) {
                        badgesHtml += `<span class="card-lms__badge" style="position: static; margin: 0; top: auto; left: auto;"><i class="fas fa-fire" style="margin-right: 4px; color: #fcd34d;"></i>Bestseller</span>`;
                    }
                    badgesHtml += `</div>`;

                    let imageHtml = '';
                    if (card.has_thumbnail) {
                        imageHtml = `
                        <div class="card-lms__image">
                          ${badgesHtml}
                          <a href="${targetLink}" style="display: block; color: inherit; text-decoration: none; height: 100%;">
                            <img src="${card.thumbnail_url}" alt="${card.name}" loading="lazy">
                          </a>
                          <button class="lms-wishlist-toggle lms-btn-icon" data-course-id="${card.id}" style="position: absolute; top: 12px; right: 12px; z-index: 10; background: rgba(0,0,0,0.5); color: white; border: 1px solid rgba(255,255,255,0.2); border-radius: 50%; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s; padding: 0;" aria-label="Toggle Wishlist">
                            <i class="${card.is_wishlisted ? 'fas' : 'far'} fa-heart" ${card.is_wishlisted ? 'style="color: #ff7b72;"' : ''}></i>
                          </button>
                          ${card.price_raw == 0 ? '<span class="card-lms__badge" style="position: absolute; bottom: 12px; left: 12px; top: auto; z-index: 10; background: linear-gradient(135deg, #10b981 0%, #059669 100%);">Free</span>' : ''}
                        </div>`;
                    } else {
                        imageHtml = `
                        <div class="card-lms__image card-lms__image--placeholder">
                          ${badgesHtml}
                          <a href="${targetLink}" style="display: flex; width: 100%; height: 100%; align-items: center; justify-content: center; color: inherit; text-decoration: none;">
                            <i class="fas fa-graduation-cap"></i>
                          </a>
                          <button class="lms-wishlist-toggle lms-btn-icon" data-course-id="${card.id}" style="position: absolute; top: 12px; right: 12px; z-index: 10; background: rgba(0,0,0,0.5); color: white; border: 1px solid rgba(255,255,255,0.2); border-radius: 50%; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s; padding: 0;" aria-label="Toggle Wishlist">
                            <i class="${card.is_wishlisted ? 'fas' : 'far'} fa-heart" ${card.is_wishlisted ? 'style="color: #ff7b72;"' : ''}></i>
                          </button>
                          ${card.price_raw == 0 ? '<span class="card-lms__badge" style="position: absolute; bottom: 12px; left: 12px; top: auto; z-index: 10; background: linear-gradient(135deg, #10b981 0%, #059669 100%);">Free</span>' : ''}
                        </div>`;
                    }

                    let instructorHtml = '';
                    if (card.instructor_name) {
                        const avatarHtml = card.instructor_avatar ? 
                            `<img src="${card.instructor_avatar}" alt="${card.instructor_name}" class="card-lms__instructor-avatar" style="width: 24px; height: 24px; border-radius: 50%; object-fit: cover;">` : 
                            `<div class="card-lms__instructor-avatar-fallback" style="width: 24px; height: 24px; border-radius: 50%; background: #333; display: flex; align-items: center; justify-content: center; font-size: 10px; color: #fff;"><i class="fas fa-user"></i></div>`;
                        instructorHtml = `
                        <div class="card-lms__instructor" style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                            <a href="${card.instructor_url || '#'}">${avatarHtml}</a>
                            <span class="card-lms__instructor-name" style="font-size: 0.85rem; color: #a1a1aa;"><a href="${card.instructor_url || '#'}" style="color: inherit; text-decoration: none;">${card.instructor_name}</a></span>
                        </div>`;
                    }

                    let metaHtml = '';
                    if (card.level) metaHtml += `<span class="card-lms__level"><i class="fas fa-signal"></i> ${card.level}</span>`;
                    if (card.duration) metaHtml += `<span class="card-lms__duration"><i class="fas fa-clock"></i> ${card.duration}</span>`;

                    let footerHtml = '';
                    if (card.is_enrolled) {
                        footerHtml = `
                            <div style="display: flex; flex-direction: column; gap: 2px;">
                                <span style="font-size: 0.7rem; color: #10b981; font-weight: 700; text-transform: uppercase; text-shadow: 0 0 10px rgba(16, 185, 129, 0.4);"><i class="fas fa-unlock"></i> Unlocked</span>
                                <span class="card-lms__price" style="font-size: 0.9rem; color: #a1a1aa; ${card.price_raw > 0 ? 'text-decoration: line-through;' : ''}">${card.price}</span>
                            </div>
                            <a href="${card.continue_url}" class="btn-lms-primary btn-lms-sm" style="background: linear-gradient(to right, #6366f1, #a855f7); border-radius: 2rem; box-shadow: 0 4px 15px rgba(168, 85, 247, 0.4); border: none; padding: 0.5rem 1.25rem;">${card.t_continue_learning || 'Continue Learning'}</a>
                        `;
                    } else {
                        footerHtml = `
                            <span class="card-lms__price ${card.price_raw == 0 ? 'card-lms__price--free' : ''}" style="font-size: 1.25rem;">${card.price}</span>
                            <a href="${card.url}" class="btn-lms-outline btn-lms-sm" style="border-radius: 2rem; border-color: rgba(255,255,255,0.2); transition: all 0.3s; padding: 0.5rem 1.25rem;" onmouseover="this.style.background='linear-gradient(to right, #6366f1, #a855f7)'; this.style.borderColor='transparent';" onmouseout="this.style.background='transparent'; this.style.borderColor='rgba(255,255,255,0.2)';">${card.t_enroll_now || 'Enroll Now'}</a>
                        `;
                    }

                    return `
                    <div class="card-lms card-lms--course" data-category="${card.category_id}" data-level="${card.level_id}" data-price="${card.price_raw}" data-rating="${card.rating || 5.0}">
                        ${imageHtml}
                        <div class="card-lms__body">
                            <h3 class="card-lms__title"><a href="${card.url}">${card.name}</a></h3>
                            ${instructorHtml}
                            ${card.short_description ? `<div class="card-lms__desc"><a href="${targetLink}" style="color: inherit; text-decoration: none;">${card.short_description.replace(/(<([^>]+)>)/gi, "")}</a></div>` : ''}
                            <div class="card-lms__meta">
                                ${metaHtml}
                                <span class="card-lms__rating" style="margin-left: auto; color: #fee140;">
                                    ${renderStars(card.rating)}
                                </span>
                            </div>
                            <div class="card-lms__footer" style="display: flex; align-items: center; justify-content: space-between;">
                                ${footerHtml}
                            </div>
                        </div>
                    </div>`;
                }

                function fetchCourses(url, paramsStr) {
                    mainArea.css({ opacity: 0.5, pointerEvents: 'none' });

                    $.ajax({
                        url: '/api/courses/search',
                        data: paramsStr,
                        method: 'GET',
                        dataType: 'json',
                        success: function(response) {
                            if (response.status === 'success') {
                                // Update URL
                                history.pushState(null, null, url.toString());

                                // Update Count
                                const countText = Drupal.t && Drupal.t('courses found') ? Drupal.t('courses found') : 'courses found';
                                $(catalog).find('.lms-catalog__count').text(response.total + ' ' + countText);

                                // Update Grid
                                let currentGrid = $(catalog).find('.grid-courses');
                                $(catalog).find('.lms-empty-state').remove();

                                if (response.courses.length > 0) {
                                    let htmlStr = '';
                                    $.each(response.courses, function(i, card) {
                                        htmlStr += renderCourseCard(card);
                                    });
                                    
                                    if (currentGrid.length) {
                                        currentGrid.html(htmlStr);
                                    } else {
                                        // create grid if it was missing (e.g. recovered from empty state)
                                        const newGrid = $('<div class="grid-courses"></div>').html(htmlStr);
                                        $(catalog).find('.lms-catalog__toolbar').after(newGrid);
                                    }
                                } else {
                                    // Empty state
                                    const notFoundText = Drupal.t && Drupal.t('No courses found') ? Drupal.t('No courses found') : 'No courses found';
                                    const adjustText = Drupal.t && Drupal.t('Try adjusting your filters') ? Drupal.t('Try adjusting your filters') : 'Try adjusting your filters';
                                    const emptyHtml = `
                                        <div class="lms-empty-state" style="text-align: center; padding: 4rem 1rem; color: #a1a1aa; border: 1px dashed rgba(255,255,255,0.2); border-radius: 1rem; margin-top: 1.5rem; background: rgba(0,0,0,0.2);">
                                            <i class="fas fa-search" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                                            <h3 style="color: white; margin-bottom: 0.5rem;">${notFoundText}</h3>
                                            <p style="margin: 0;">${adjustText}</p>
                                        </div>
                                    `;
                                    if (currentGrid.length) {
                                        currentGrid.remove();
                                    }
                                    $(catalog).find('.lms-catalog__toolbar').after(emptyHtml);
                                }

                                // Update Pager HTML manually based on total pages
                                $(catalog).find('nav.pager, nav.lms-pagination').remove();

                                if (response.pager && response.pager.total_pages > 1) {
                                    const cp = parseInt(response.pager.current_page, 10);
                                    const tp = parseInt(response.pager.total_pages, 10);
                                    
                                    // Use a smaller pager size if it's placed at the top toolbar
                                    let pagerHtml = '<nav class="lms-pagination" role="navigation">';
                                    
                                    // Prev
                                    if (cp > 0) {
                                        pagerHtml += `<a href="?page=${cp-1}" class="lms-pagination__item" title="Go to previous page" rel="prev">‹</a>`;
                                    }

                                    // Limit visible pages
                                    let startPage = Math.max(0, cp - 2);
                                    let endPage = Math.min(tp - 1, cp + 2);

                                    // First page indicators if deep in
                                    if (startPage > 0) {
                                        pagerHtml += `<a href="?page=0" class="lms-pagination__item">1</a>`;
                                        if (startPage > 1) pagerHtml += `<span class="lms-pagination__item lms-pagination__item--disabled">&hellip;</span>`;
                                    }

                                    for (let i = startPage; i <= endPage; i++) {
                                        if (i === cp) {
                                            pagerHtml += `<span class="lms-pagination__item lms-pagination__item--active">${i+1}</span>`;
                                        } else {
                                            pagerHtml += `<a href="?page=${i}" class="lms-pagination__item">${i+1}</a>`;
                                        }
                                    }

                                    // Last page indicators
                                    if (endPage < tp - 1) {
                                        if (endPage < tp - 2) pagerHtml += `<span class="lms-pagination__item lms-pagination__item--disabled">&hellip;</span>`;
                                        pagerHtml += `<a href="?page=${tp-1}" class="lms-pagination__item">${tp}</a>`;
                                    }

                                    // Next
                                    if (cp < tp - 1) {
                                        pagerHtml += `<a href="?page=${cp+1}" class="lms-pagination__item" title="Go to next page" rel="next">›</a>`;
                                    }

                                    pagerHtml += '</nav>';
                                    
                                    // Inject Pagination at the bottom only
                                    $(catalog).find('.lms-catalog__main').append(pagerHtml);
                                    
                                    bindPagerEvents();
                                }
                            }
                        },
                        complete: function() {
                            mainArea.css({ opacity: 1, pointerEvents: 'auto' });
                        }
                    });
                }

                function buildQueryStr(page = null) {
                    const url = new URL(window.location.href);
                    const params = url.searchParams;
                    
                    params.delete('search');
                    params.delete('category[]');
                    params.delete('level[]');
                    params.delete('price[]');
                    params.delete('rating[]');
                    params.delete('sort');
                    
                    if (page !== null) {
                        params.set('page', page);
                    } else {
                        params.delete('page');
                    }

                    if (searchInput.length && searchInput.val().trim() !== '') {
                        params.set('search', searchInput.val().trim());
                    }
                    if (categorySelect.length && categorySelect.val()) {
                        params.append('category[]', categorySelect.val());
                    }
                    $(catalog).find('input[type="checkbox"][data-filter-type="category"]:checked').each(function() {
                        if (!categorySelect.length || categorySelect.val() !== $(this).val()) {
                            params.append('category[]', $(this).val());
                        }
                    });
                    $(catalog).find('input[type="checkbox"][data-filter-type="level"]:checked').each(function() { params.append('level[]', $(this).val()); });
                    $(catalog).find('input[type="checkbox"][data-filter-type="price"]:checked').each(function() { params.append('price[]', $(this).val()); });
                    $(catalog).find('input[type="checkbox"][data-filter-type="rating"]:checked').each(function() { params.append('rating[]', $(this).val()); });
                    
                    if (sortSelect.length && sortSelect.val() !== 'Most Popular') {
                        params.set('sort', sortSelect.val());
                    }

                    return { url, paramsStr: params.toString() };
                }

                function triggerSearch(page = null) {
                    const data = buildQueryStr(page);
                    // Prevent pushing the exact same URL
                    if (window.location.href !== data.url.toString() || page !== null) {
                        fetchCourses(data.url, data.paramsStr);
                    }
                }

                function handleInputWithDebounce() {
                    if (timeoutId) clearTimeout(timeoutId);
                    timeoutId = setTimeout(() => triggerSearch(), 600);
                }

                function handleChange() {
                    if (timeoutId) clearTimeout(timeoutId);
                    triggerSearch();
                }

                function bindPagerEvents() {
                    $(catalog).find('.pager__item a, a.lms-pagination__item').off('click').on('click', function(e) {
                        e.preventDefault();
                        const href = $(this).attr('href');
                        const urlParams = new URLSearchParams(href.split('?')[1]);
                        const page = urlParams.get('page') || 0;
                        triggerSearch(page);
                        // smooth scroll to top of catalog
                        $('html, body').animate({
                            scrollTop: $(catalog).offset().top - 40
                        }, 300);
                    });
                }

                // Bind inputs
                if (searchInput.length) {
                    searchInput.on('input', handleInputWithDebounce).on('keypress', function(e) {
                        if (e.key === 'Enter') {
                            e.preventDefault();
                            if (timeoutId) clearTimeout(timeoutId);
                            triggerSearch();
                        }
                    });
                }
                
                if (categorySelect.length) categorySelect.on('change', handleChange);
                if (sortSelect.length) sortSelect.on('change', handleChange);
                $(catalog).find('input[type="checkbox"]').on('change', handleChange);

                // Show more categories toggle
                $(catalog).find('.lms-catalog__show-more').on('click', function(e) {
                    e.preventDefault();
                    const btn = $(this);
                    const isExpanded = btn.data('expanded');
                    const hiddenItems = $(catalog).find('.lms-js-hidden-cats');
                    
                    if (!isExpanded) {
                        hiddenItems.slideDown(200);
                        btn.text('- ' + Drupal.t('Show Less'));
                        btn.data('expanded', true);
                    } else {
                        hiddenItems.slideUp(200);
                        const originalCount = btn.data('hidden-count');
                        btn.text('+ ' + originalCount + ' ' + Drupal.t('More Categories'));
                        btn.data('expanded', false);
                    }
                });
                
                // Initial pager bind
                bindPagerEvents();
            });
        }
    };

    /**
     * Back to Top Button Interaction.
     */
    Drupal.behaviors.lmsBackToTop = {
        attach: function (context) {
            once('lms-back-to-top', '#lms-back-to-top', context).forEach(function (btn) {
                // Show/hide based on scroll position
                window.addEventListener('scroll', function () {
                    if (window.scrollY > 300) {
                        btn.classList.add('lms-back-to-top--visible');
                    } else {
                        btn.classList.remove('lms-back-to-top--visible');
                    }
                });

                // Scroll to top on click
                btn.addEventListener('click', function (e) {
                    e.preventDefault();
                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });
                });
            });
        }
    };

    Drupal.behaviors.lmsCourseFilters = {
        attach: function (context) {
            once('lms-course-filters', '[data-course-filter-root]', context).forEach(function (root) {
                const buttons = root.querySelectorAll('[data-course-filter]');
                const items = root.querySelectorAll('[data-course-status]');

                if (!buttons.length || !items.length) {
                    return;
                }

                function applyFilter(filterValue) {
                    buttons.forEach(function (button) {
                        const isActive = button.getAttribute('data-course-filter') === filterValue;
                        button.classList.toggle('is-active', isActive);
                        button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
                    });

                    items.forEach(function (item) {
                        const itemStatus = item.getAttribute('data-course-status');
                        item.hidden = filterValue !== 'all' && itemStatus !== filterValue;
                    });
                }

                buttons.forEach(function (button) {
                    button.addEventListener('click', function () {
                        applyFilter(button.getAttribute('data-course-filter') || 'all');
                    });
                });

                applyFilter('all');
            });
        }
    };

})(jQuery, Drupal, once);
