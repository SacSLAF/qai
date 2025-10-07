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

            // Handle dropdown toggle - only close when clicking the toggle itself
            const dropdownToggle = document.querySelector('.qa-dropdown-toggle');
            if (dropdownToggle) {
                dropdownToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    const dropdownMenu = this.nextElementSibling;
                    dropdownMenu.classList.toggle('show');
                });
            }

            // Handle tab selection for main nav links (non-dropdown)
            const mainNavLinks = document.querySelectorAll('.nav-link:not(.qa-dropdown-toggle)');
            mainNavLinks.forEach(item => {
                item.addEventListener('click', function(e) {
                    e.preventDefault();

                    // Remove active class from all main nav links
                    document.querySelectorAll('.nav-link:not(.qa-dropdown-toggle)').forEach(tab => {
                        tab.classList.remove('active');
                    });

                    // Remove active class from QA dropdown toggle
                    document.querySelector('.qa-dropdown-toggle').classList.remove('active');

                    // Close dropdown menu
                    const dropdownMenu = document.querySelector('.qa-dropdown-menu');
                    if (dropdownMenu) {
                        dropdownMenu.classList.remove('show');
                    }

                    // Add active class to clicked tab
                    this.classList.add('active');

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
                });
            });

            // Handle tab selection for dropdown items
            const dropdownItems = document.querySelectorAll('.qa-dropdown-item');
            dropdownItems.forEach(item => {
                item.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation(); // Prevent event from bubbling up

                    // Remove active class from all main nav links
                    document.querySelectorAll('.nav-link:not(.qa-dropdown-toggle)').forEach(tab => {
                        tab.classList.remove('active');
                    });

                    // Remove active class from all dropdown items
                    document.querySelectorAll('.qa-dropdown-item').forEach(tab => {
                        tab.classList.remove('active');
                    });

                    // Add active class to clicked dropdown item
                    this.classList.add('active');

                    // Activate the parent dropdown toggle
                    document.querySelector('.qa-dropdown-toggle').classList.add('active');

                    // Keep dropdown menu open
                    const dropdownMenu = document.querySelector('.qa-dropdown-menu');
                    if (dropdownMenu) {
                        dropdownMenu.classList.add('show');
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
                });
            });

            // Close dropdown when clicking outside, but only if it's open
            document.addEventListener('click', function(e) {
                const dropdown = document.querySelector('.qa-dropdown');
                const dropdownMenu = document.querySelector('.qa-dropdown-menu');

                if (dropdown && !dropdown.contains(e.target) && dropdownMenu.classList.contains('show')) {
                    dropdownMenu.classList.remove('show');
                }
            });

            // Prevent dropdown from closing when clicking inside it
            const dropdownMenu = document.querySelector('.qa-dropdown-menu');
            if (dropdownMenu) {
                dropdownMenu.addEventListener('click', function(e) {
                    e.stopPropagation();
                });
            }
        });