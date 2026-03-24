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
     * Course Catalog Filtering.
     */
    Drupal.behaviors.lmsCatalogFilter = {
        attach: function(context) {
            once('lms-catalog-filter', '.lms-catalog', context).forEach(function(catalog) {
                const searchInput = catalog.querySelector('#course-search');
                const categorySelect = catalog.querySelector('#category-filter');
                const categoryChecks = catalog.querySelectorAll('input[type="checkbox"][data-filter-type="category"]');
                const levelChecks = catalog.querySelectorAll('input[type="checkbox"][data-filter-type="level"]');
                const priceChecks = catalog.querySelectorAll('input[type="checkbox"][data-filter-type="price"]');
                const ratingChecks = catalog.querySelectorAll('input[type="checkbox"][data-filter-type="rating"]');
                const sortSelect = catalog.querySelector('#sort-courses');
                const grid = catalog.querySelector('.grid-courses');
                const countElem = catalog.querySelector('.lms-catalog__count');
                const emptyState = catalog.querySelector('.lms-empty-state');
                
                if (!grid && !emptyState) return;
                
                // Allow recovering cards if initially empty
                const cards = grid ? Array.from(grid.querySelectorAll('.card-lms--course')) : [];

                function filterCourses() {
                    const searchText = searchInput ? searchInput.value.toLowerCase() : '';
                    const selectedCatObj = categorySelect ? categorySelect.value : '';
                    
                    const checkedCats = Array.from(categoryChecks).filter(c => c.checked).map(c => c.value);
                    if (selectedCatObj) checkedCats.push(selectedCatObj);
                    
                    const checkedLevels = Array.from(levelChecks).filter(c => c.checked).map(c => c.value);
                    const checkedPrices = Array.from(priceChecks).filter(c => c.checked).map(c => c.value);
                    const checkedRatings = Array.from(ratingChecks).filter(c => c.checked).map(c => parseInt(c.value, 10));

                    let visibleCount = 0;

                    cards.forEach(card => {
                        const title = card.querySelector('.card-lms__title').textContent.toLowerCase();
                        const cat = card.dataset.category;
                        const level = card.dataset.level;
                        const priceRaw = parseFloat(card.dataset.price || '0');
                        const rating = parseInt(card.dataset.rating || '5', 10);
                        
                        let match = true;

                        // Search
                        if (searchText && !title.includes(searchText)) match = false;
                        
                        // Category
                        if (match && checkedCats.length > 0 && !checkedCats.includes(cat)) match = false;
                        
                        // Level
                        if (match && checkedLevels.length > 0 && !checkedLevels.includes(level)) match = false;

                        // Price
                        if (match && checkedPrices.length > 0) {
                            const isFree = priceRaw === 0;
                            const isPaid = priceRaw > 0;
                            if (checkedPrices.includes('free') && !isFree && !checkedPrices.includes('paid')) match = false;
                            if (checkedPrices.includes('paid') && !isPaid && !checkedPrices.includes('free')) match = false;
                            if (!checkedPrices.includes('free') && checkedPrices.includes('paid') && !isPaid) match = false;
                            if (!checkedPrices.includes('paid') && checkedPrices.includes('free') && !isFree) match = false;
                        }

                        // Rating
                        if (match && checkedRatings.length > 0) {
                            if (!checkedRatings.includes(Math.floor(rating))) {
                                match = false;
                            }
                        }

                        if (match) {
                            card.style.display = '';
                            visibleCount++;
                        } else {
                            card.style.display = 'none';
                        }
                    });

                    // Update count
                    if (countElem) {
                        countElem.textContent = `${visibleCount} courses found`;
                    }
                    
                    // Handle empty state manually if we don't reload page
                    if (grid) {
                       grid.style.display = visibleCount > 0 ? 'grid' : 'none';
                    }
                }

                function sortCourses() {
                    if (!sortSelect || !grid) return;
                    const val = sortSelect.value;
                    const allCards = Array.from(grid.querySelectorAll('.card-lms--course'));
                    
                    allCards.sort((a, b) => {
                       if (val.includes('Low-High')) {
                           const pa = parseFloat(a.dataset.price || '0');
                           const pb = parseFloat(b.dataset.price || '0');
                           return pa - pb;
                       } else if (val.includes('Newest')) {
                           // Assume original DOM order was newest, but without a date field we just reverse or mock
                           return -1; 
                       }
                       return -1; // Popular (mock)
                    });
                    
                    allCards.forEach(c => grid.appendChild(c));
                }

                const inputs = [searchInput, categorySelect, ...categoryChecks, ...levelChecks, ...priceChecks, ...ratingChecks];
                inputs.forEach(input => {
                    if (input) {
                        input.addEventListener('input', filterCourses);
                        input.addEventListener('change', filterCourses);
                    }
                });

                if (sortSelect) {
                    sortSelect.addEventListener('change', sortCourses);
                }
            });
        }
    };

})(jQuery, Drupal, once);
