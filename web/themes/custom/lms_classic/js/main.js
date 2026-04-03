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
                        nav.classList.toggle('lms-navbar__nav--open');
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
                const panels = document.querySelectorAll('.lms-tab-panel');

                function activateTab(hash) {
                    if (!hash) return false;
                    
                    const targetLink = tabContainer.querySelector(`a[href="${hash}"]`);
                    const targetPanel = document.querySelector(hash);
                    
                    if (targetLink && targetPanel) {
                        links.forEach(l => l.classList.remove('lms-tabs__link--active'));
                        panels.forEach(p => p.style.display = 'none');
                        
                        targetLink.classList.add('lms-tabs__link--active');
                        targetPanel.style.display = 'block';
                        targetPanel.style.animation = 'fadeIn 0.3s ease';
                        return true;
                    }
                    return false;
                }

                // Initial activation based on URL hash - Delayed to ensure DOM is ready
                setTimeout(() => {
                    if (window.location.hash) {
                        activateTab(window.location.hash);
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
     * Course Catalog Filtering using AJAX and History API
     */
    Drupal.behaviors.lmsCatalogFilter = {
        attach: function(context, settings) {
            once('lms-catalog-filter', '.lms-catalog', context).forEach(function(catalog) {
                const searchInput = catalog.querySelector('#course-search');
                const categorySelect = catalog.querySelector('#category-filter');
                const categoryChecks = catalog.querySelectorAll('input[type="checkbox"][data-filter-type="category"]');
                const levelChecks = catalog.querySelectorAll('input[type="checkbox"][data-filter-type="level"]');
                const priceChecks = catalog.querySelectorAll('input[type="checkbox"][data-filter-type="price"]');
                const ratingChecks = catalog.querySelectorAll('input[type="checkbox"][data-filter-type="rating"]');
                const mainArea = catalog.querySelector('.lms-catalog__main');
                
                let timeoutId = null;

                // Function to fetch and replace main course area seamlessly
                function fetchAndReplace(urlStr) {
                    if (mainArea) {
                        mainArea.style.opacity = '0.4';
                        mainArea.style.pointerEvents = 'none';
                        mainArea.style.transition = 'opacity 0.3s ease';
                    }
                    
                    fetch(urlStr, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                        .then(response => response.text())
                        .then(html => {
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(html, 'text/html');
                            const newMain = doc.querySelector('.lms-catalog__main');
                            if (newMain && mainArea) {
                                mainArea.innerHTML = newMain.innerHTML;
                                mainArea.style.opacity = '1';
                                mainArea.style.pointerEvents = 'auto';
                                
                                bindPagerLinks();
                                Drupal.attachBehaviors(mainArea, settings); // Re-run behaviors
                            }
                        })
                        .catch(err => {
                            console.error('AJAX Filter Error', err);
                            window.location.href = urlStr; // Fallback to full reload on error
                        });
                }

                function buildQueryAndFetch(pageUrl = null) {
                    let urlStr = pageUrl;
                    
                    if (!urlStr) {
                        const url = new URL(window.location.href);
                        const params = url.searchParams;
                        
                        // Clear existing filters
                        params.delete('search');
                        params.delete('category[]');
                        params.delete('level[]');
                        params.delete('price[]');
                        params.delete('rating[]');
                        params.delete('sort');
                        params.delete('page'); // Reset pagination

                        if (searchInput && searchInput.value.trim() !== '') {
                            params.set('search', searchInput.value.trim());
                        }
                        if (categorySelect && categorySelect.value) {
                            params.append('category[]', categorySelect.value);
                        }
                        Array.from(categoryChecks).forEach(c => {
                            if (c.checked && (!categorySelect || categorySelect.value !== c.value)) {
                                params.append('category[]', c.value);
                            }
                        });
                        Array.from(levelChecks).forEach(c => { if (c.checked) params.append('level[]', c.value); });
                        Array.from(priceChecks).forEach(c => { if (c.checked) params.append('price[]', c.value); });
                        Array.from(ratingChecks).forEach(c => { if (c.checked) params.append('rating[]', c.value); });
                        
                        // Re-query dynamically since sort was replaced in DOM
                        const sortSelect = catalog.querySelector('#sort-courses');
                        if (sortSelect && sortSelect.value !== 'Most Popular') {
                            params.set('sort', sortSelect.value);
                        }

                        urlStr = url.toString();
                    }

                    if (window.location.href !== urlStr) {
                        window.history.pushState(null, '', urlStr);
                        fetchAndReplace(urlStr);
                    }
                }

                function bindPagerLinks() {
                    if (!mainArea) return;
                    const pagerLinks = mainArea.querySelectorAll('.pager__link, .pager__item a');
                    pagerLinks.forEach(link => {
                        link.addEventListener('click', function(e) {
                            e.preventDefault();
                            buildQueryAndFetch(this.href);
                            
                            const y = catalog.getBoundingClientRect().top + window.scrollY - 100;
                            window.scrollTo({top: y, behavior: 'smooth'});
                        });
                    });
                }

                function handleInputWithDebounce() {
                    if (timeoutId) clearTimeout(timeoutId);
                    timeoutId = setTimeout(() => buildQueryAndFetch(), 600);
                }

                function handleChange() {
                    if (timeoutId) clearTimeout(timeoutId);
                    buildQueryAndFetch();
                }

                // Event Listeners for inputs (Sidebar not replaced by AJAX, so safe to bind once)
                if (searchInput) {
                    searchInput.addEventListener('input', handleInputWithDebounce);
                    searchInput.addEventListener('keypress', function(e) {
                        if (e.key === 'Enter') {
                            e.preventDefault();
                            handleChange();
                        }
                    });
                }
                
                if (categorySelect) categorySelect.addEventListener('change', handleChange);
                const checkboxes = [...Array.from(categoryChecks), ...Array.from(levelChecks), ...Array.from(priceChecks), ...Array.from(ratingChecks)];
                checkboxes.forEach(chk => chk.addEventListener('change', handleChange));

                // Delegate event for Sort because it gets destroyed during HTML replace
                catalog.addEventListener('change', function(e) {
                    if (e.target && e.target.id === 'sort-courses') {
                        handleChange();
                    }
                });

                // Init Custom Pager Links
                bindPagerLinks();
                
                // Handle Browser Back/Forward buttons gracefully
                window.addEventListener('popstate', function() {
                    fetchAndReplace(window.location.href);
                });
            });
        }
    };

})(jQuery, Drupal, once);
