(function (Drupal, once) {
  Drupal.behaviors.lmsInstructorStudents = {
    attach(context) {
      once('lmsInstructorStudents', '[data-instructor-roster-root]', context).forEach((root) => {
        const table = root.querySelector('[data-instructor-roster]');
        const rows = Array.from(root.querySelectorAll('[data-instructor-roster-row]'));
        const pageSize = Math.max(parseInt(root.dataset.pageSize || '20', 10) || 20, 1);
        const studentInput = root.querySelector('[data-instructor-filter="student"]');
        const courseInput = root.querySelector('[data-instructor-filter="course"]');
        const statusInput = root.querySelector('[data-instructor-filter="status"]');
        const clearButton = root.querySelector('[data-instructor-filter-reset]');
        const countNode = root.querySelector('[data-instructor-filter-count]');
        const emptyNode = root.querySelector('[data-instructor-empty]');
        const pageSummaryNode = root.querySelector('[data-instructor-page-summary]');
        const paginationNode = root.querySelector('[data-instructor-pagination]');
        const paginationPagesNode = root.querySelector('[data-instructor-pagination-pages]');
        const previousButton = root.querySelector('[data-instructor-page-prev]');
        const nextButton = root.querySelector('[data-instructor-page-next]');

        if (!table || rows.length === 0) {
          return;
        }

        const normalize = (value) => value.trim().toLowerCase();
        let currentPage = 1;
        let filteredRows = rows;

        const createPageButton = (label, page, isActive) => {
          const button = document.createElement('button');
          button.type = 'button';
          button.className = `lms-instructor-roster__page-button${isActive ? ' is-active' : ''}`;
          button.textContent = String(label);
          button.setAttribute('aria-label', `Go to page ${label}`);
          if (isActive) {
            button.setAttribute('aria-current', 'page');
          }

          button.addEventListener('click', () => {
            currentPage = page;
            renderRows();
          });

          return button;
        };

        const createGap = () => {
          const gap = document.createElement('span');
          gap.className = 'lms-instructor-roster__page-gap';
          gap.textContent = '...';
          return gap;
        };

        const getPageTokens = (totalPages, page) => {
          if (totalPages <= 7) {
            return Array.from({ length: totalPages }, (_, index) => index + 1);
          }

          if (page <= 4) {
            return [1, 2, 3, 4, 5, 'gap', totalPages];
          }

          if (page >= totalPages - 3) {
            return [1, 'gap', totalPages - 4, totalPages - 3, totalPages - 2, totalPages - 1, totalPages];
          }

          return [1, 'gap', page - 1, page, page + 1, 'gap', totalPages];
        };

        const renderPagination = (totalPages) => {
          if (!paginationNode || !paginationPagesNode || !previousButton || !nextButton) {
            return;
          }

          paginationNode.hidden = totalPages <= 1;
          paginationPagesNode.innerHTML = '';

          previousButton.disabled = currentPage <= 1;
          nextButton.disabled = currentPage >= totalPages;

          if (totalPages <= 1) {
            return;
          }

          getPageTokens(totalPages, currentPage).forEach((token) => {
            if (token === 'gap') {
              paginationPagesNode.appendChild(createGap());
              return;
            }

            paginationPagesNode.appendChild(createPageButton(token, token, token === currentPage));
          });
        };

        const renderRows = () => {
          const filteredCount = filteredRows.length;
          const totalPages = filteredCount === 0 ? 0 : Math.ceil(filteredCount / pageSize);

          if (totalPages > 0 && currentPage > totalPages) {
            currentPage = totalPages;
          }

          const startIndex = totalPages === 0 ? 0 : (currentPage - 1) * pageSize;
          const endIndex = totalPages === 0 ? 0 : Math.min(startIndex + pageSize, filteredCount);
          const rowsOnPage = filteredRows.slice(startIndex, endIndex);

          rows.forEach((row) => {
            row.hidden = true;
          });

          rowsOnPage.forEach((row) => {
            row.hidden = false;
          });

          if (countNode) {
            countNode.textContent = String(filteredCount);
          }

          if (emptyNode) {
            emptyNode.hidden = filteredCount !== 0;
          }

          if (pageSummaryNode) {
            if (filteredCount === 0) {
              pageSummaryNode.textContent = 'No rows to display';
            }
            else {
              pageSummaryNode.textContent = `Showing ${startIndex + 1}-${endIndex} of ${filteredCount}`;
            }
          }

          renderPagination(totalPages);
        };

        const applyFilters = () => {
          const studentQuery = normalize(studentInput ? studentInput.value : '');
          const courseQuery = normalize(courseInput ? courseInput.value : '');
          const statusQuery = normalize(statusInput ? statusInput.value : '');

          filteredRows = rows.filter((row) => {
            const studentValue = row.dataset.student || '';
            const courseValue = row.dataset.course || '';
            const statusValue = row.dataset.status || '';
            const matchesStudent = studentQuery === '' || studentValue.includes(studentQuery);
            const matchesCourse = courseQuery === '' || courseValue.includes(courseQuery);
            const matchesStatus = statusQuery === '' || statusValue === statusQuery;
            return matchesStudent && matchesCourse && matchesStatus;
          });

          currentPage = 1;
          renderRows();
        };

        [studentInput, courseInput].forEach((input) => {
          if (input) {
            input.addEventListener('input', applyFilters);
          }
        });

        if (statusInput) {
          statusInput.addEventListener('change', applyFilters);
        }

        if (clearButton) {
          clearButton.addEventListener('click', () => {
            if (studentInput) {
              studentInput.value = '';
            }
            if (courseInput) {
              courseInput.value = '';
            }
            if (statusInput) {
              statusInput.value = '';
            }
            applyFilters();
          });
        }

        if (previousButton) {
          previousButton.addEventListener('click', () => {
            if (currentPage > 1) {
              currentPage -= 1;
              renderRows();
            }
          });
        }

        if (nextButton) {
          nextButton.addEventListener('click', () => {
            const totalPages = filteredRows.length === 0 ? 0 : Math.ceil(filteredRows.length / pageSize);
            if (currentPage < totalPages) {
              currentPage += 1;
              renderRows();
            }
          });
        }

        applyFilters();
      });
    },
  };
})(Drupal, once);
