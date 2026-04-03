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

})(jQuery, Drupal, once);
