 // Handle tab selection
        document.addEventListener("DOMContentLoaded", function() {
            // Set initial active tab and content - using OSH Manual as default
            const initialTab = document.querySelector('[data-bs-target="#osh_man"]');
            const initialPane = document.querySelector('#osh_man');
            
            if (initialTab && initialPane) {
                initialTab.classList.add('active');
                initialPane.classList.add('show', 'active');
                
                // Activate the parent dropdown toggle
                const parentDropdown = initialTab.closest('.qa-dropdown');
                if (parentDropdown) {
                    const dropdownToggle = parentDropdown.querySelector('.qa-dropdown-toggle');
                    if (dropdownToggle) {
                        dropdownToggle.classList.add('active');
                    }
                }
            }
            
            // Handle dropdown toggle for ALL dropdowns
            const dropdownToggles = document.querySelectorAll('.qa-dropdown-toggle');
            dropdownToggles.forEach(toggle => {
                toggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    // Close other dropdowns
                    dropdownToggles.forEach(otherToggle => {
                        if (otherToggle !== toggle) {
                            const otherMenu = otherToggle.nextElementSibling;
                            if (otherMenu) {
                                otherMenu.classList.remove('show');
                            }
                        }
                    });
                    
                    // Toggle current dropdown
                    const dropdownMenu = this.nextElementSibling;
                    if (dropdownMenu) {
                        dropdownMenu.classList.toggle('show');
                    }
                });
            });
            
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
                    
                    // Remove active class from all dropdown toggles
                    document.querySelectorAll('.qa-dropdown-toggle').forEach(toggle => {
                        toggle.classList.remove('active');
                    });
                    
                    // Add active class to clicked tab
                    this.classList.add('active');
                    
                    // If this is a dropdown item, activate the parent dropdown toggle
                    if (this.classList.contains('qa-dropdown-item')) {
                        const parentDropdown = this.closest('.qa-dropdown');
                        if (parentDropdown) {
                            const dropdownToggle = parentDropdown.querySelector('.qa-dropdown-toggle');
                            if (dropdownToggle) {
                                dropdownToggle.classList.add('active');
                            }
                        }
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
                    
                    // Close all dropdowns after selection
                    document.querySelectorAll('.qa-dropdown-menu').forEach(menu => {
                        menu.classList.remove('show');
                    });
                });
            });
            
            // Close dropdowns when clicking outside
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.qa-dropdown')) {
                    document.querySelectorAll('.qa-dropdown-menu').forEach(menu => {
                        menu.classList.remove('show');
                    });
                }
            });
            
            // Handle escape key to close dropdowns
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    document.querySelectorAll('.qa-dropdown-menu').forEach(menu => {
                        menu.classList.remove('show');
                    });
                }
            });
        });