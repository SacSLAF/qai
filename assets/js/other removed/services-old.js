 // Handle tab selection
        document.addEventListener("DOMContentLoaded", function() {
            <?php if ($show_pdf): ?>
                // If we're showing a PDF, make sure the audits plan tab is active
                document.querySelector('[data-bs-target="#audits_plan"]').classList.add('active');
                document.querySelector('.qa-dropdown-toggle').classList.add('active');
            <?php else: ?>
                // Set initial active tab
                const initialTab = document.querySelector('[data-bs-target="#audits_plan"]');
                if (initialTab) {
                    initialTab.classList.add('active');
                    document.querySelector('.qa-dropdown-toggle').classList.add('active');
                }
            <?php endif; ?>

            // Handle dropdown toggle
            const dropdownToggle = document.querySelector('.qa-dropdown-toggle');
            if (dropdownToggle) {
                dropdownToggle.addEventListener('click', function() {
                    const dropdownMenu = this.nextElementSibling;
                    dropdownMenu.classList.toggle('show');
                });
            }

            // Handle tab selection
            const tabItems = document.querySelectorAll('.nav-link:not(.qa-dropdown-toggle), .qa-dropdown-item');
            tabItems.forEach(item => {
                item.addEventListener('click', function(e) {
                    e.preventDefault();

                    // Remove active class from all main nav links
                    document.querySelectorAll('.nav-link:not(.qa-dropdown-toggle)').forEach(tab => {
                        tab.classList.remove('active');
                    });

                    // Remove active class from all dropdown items
                    document.querySelectorAll('.qa-dropdown-item').forEach(tab => {
                        tab.classList.remove('active');
                    });

                    // Remove active class from QA dropdown toggle if clicking on a different category
                    if (!this.classList.contains('qa-dropdown-item')) {
                        document.querySelector('.qa-dropdown-toggle').classList.remove('active');
                    }

                    // Add active class to clicked tab
                    this.classList.add('active');

                    // If this is a dropdown item, also activate the parent dropdown toggle
                    if (this.classList.contains('qa-dropdown-item')) {
                        document.querySelector('.qa-dropdown-toggle').classList.add('active');
                    }

                    // Show the target tab content
                    const targetId = this.getAttribute('data-bs-target');
                    const targetPane = document.querySelector(targetId);

                    // Hide all tab panes
                    document.querySelectorAll('.tab-pane').forEach(pane => {
                        pane.classList.remove('show', 'active');
                    });

                    // Show the selected tab pane
                    if (targetPane) {
                        targetPane.classList.add('show', 'active');
                    }

                    // Close dropdown after selection
                    const dropdownMenu = document.querySelector('.qa-dropdown-menu');
                    if (dropdownMenu && this.classList.contains('qa-dropdown-item')) {
                        dropdownMenu.classList.remove('show');
                    }
                });
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                const dropdown = document.querySelector('.qa-dropdown');
                if (dropdown && !dropdown.contains(e.target)) {
                    const dropdownMenu = document.querySelector('.qa-dropdown-menu');
                    if (dropdownMenu) {
                        dropdownMenu.classList.remove('show');
                    }
                }
            });
        });