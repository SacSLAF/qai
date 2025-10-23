$(document).ready(function() {
            $('.competencyTable').DataTable();
            const dataTableConfig = {
                "pageLength": 10,
                "lengthMenu": [10, 25, 50, 100],
                "order": [
                    [2, "desc"]
                ],
                "language": {
                    "search": "Search:",
                    "lengthMenu": "Show _MENU_ entries",
                    "info": "Showing _START_ to _END_ of _TOTAL_ entries",
                    "paginate": {
                        "previous": "Previous",
                        "next": "Next"
                    }
                }
            };

            // Initialize all tables with error handling
            const tableIds = [
                'qaReportsTable',
                'qaCheckListTable',
                // 'competencyTable',
                'latitudeTable',
                'modificationTable',
                'rndTable'
            ];

            tableIds.forEach(tableId => {
                if ($('#' + tableId).length) {
                    $('#' + tableId).DataTable(dataTableConfig);
                }
            });
        });

        // Vehicle Emission Test Tables - Initialize on tab show with better handling
        function initializeVehicleTables() {
            console.log('Initializing vehicle tables...');

            // Diesel Table
            if ($('#publicDieselTable').length && !$.fn.DataTable.isDataTable('#publicDieselTable')) {
                console.log('Found Diesel table, initializing...');
                $('#publicDieselTable').DataTable({
                    ...dataTableConfig,
                    "initComplete": function(settings, json) {
                        console.log('Diesel table initialized with', this.api().rows().count(), 'rows');
                    }
                });
            } else if ($.fn.DataTable.isDataTable('#publicDieselTable')) {
                console.log('Diesel table already initialized');
                $('#publicDieselTable').DataTable().draw();
            }

            // Petrol Table
            if ($('#publicPetrolTable').length && !$.fn.DataTable.isDataTable('#publicPetrolTable')) {
                console.log('Found Petrol table, initializing...');
                $('#publicPetrolTable').DataTable({
                    ...dataTableConfig,
                    "initComplete": function(settings, json) {
                        console.log('Petrol table initialized with', this.api().rows().count(), 'rows');
                    }
                });
            } else if ($.fn.DataTable.isDataTable('#publicPetrolTable')) {
                console.log('Petrol table already initialized');
                $('#publicPetrolTable').DataTable().draw();
            }
        }

        // Initialize vehicle tables when their parent tab is shown
        $('a[data-bs-target="#vehicle_test_reports"]').on('click', function() {
            console.log('Vehicle test reports nav item clicked');
            setTimeout(initializeVehicleTables, 300);
        });

        // Also initialize when fuel type tabs are shown
        $('#public-diesel-tab, #public-petrol-tab').on('shown.bs.tab', function(e) {
            console.log('Fuel type tab shown:', e.target.id);
            setTimeout(initializeVehicleTables, 100);
        });

        // If we're already on the vehicle test reports tab, initialize immediately
        if ($('#vehicle_test_reports').hasClass('active') || $('#vehicle_test_reports').hasClass('show')) {
            console.log('Vehicle test reports tab is active on load');
            setTimeout(initializeVehicleTables, 500);
        }

        // Handle tab selection
        document.addEventListener("DOMContentLoaded", function() {

            // PDF Modal functionality
            const pdfModal = document.getElementById('pdfModal');
            const pdfFrame = document.getElementById('pdfFrame');

            pdfModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const pdfUrl = button.getAttribute('data-pdf-url');
                pdfFrame.src = pdfUrl;
            });

            pdfModal.addEventListener('hidden.bs.modal', function() {
                pdfFrame.src = ""; // Clear iframe to stop PDF from running
            });

            // Details Modal functionality
            const detailsModal = document.getElementById('detailsModal');
            const detailsModalTitle = document.getElementById('detailsModalTitle');
            const detailsModalBody = document.getElementById('detailsModalBody');

            detailsModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const recordType = button.getAttribute('data-record-type');

                // Set modal title based on record type
                let title = 'Record Details';
                switch (recordType) {
                    case 'qa_report':
                        title = 'QA Reports Details';
                        break;
                    case 'qa_check_list':
                        title = 'Audit Checklist Details';
                        break;
                    case 'aircraft_competency':
                        title = 'Aircraft Competency Details';
                        break;
                    case 'latitude':
                        title = 'Latitude Record Details';
                        break;
                }
                detailsModalTitle.textContent = title;

                // Generate modal content based on record type
                let content = '';
                switch (recordType) {
                    case 'qa_report':
                    case 'qa_check_list':
                        content = generateDocumentDetails(button);
                        break;
                    case 'aircraft_competency':
                        content = generateAircraftCompetencyDetails(button);
                        break;
                    case 'latitude':
                        content = generateLatitudeDetails(button);
                        break;
                    default:
                        content = '<p>No details available.</p>';
                }

                detailsModalBody.innerHTML = content;
            });

            detailsModal.addEventListener('hidden.bs.modal', function() {
                detailsModalBody.innerHTML = '';
            });

            // Helper function to format empty values
            function formatValue(value) {
                if (!value || value === 'null' || value === 'undefined' || value === '0000-00-00') {
                    return '<span class="empty-value">Not provided</span>';
                }
                return value;
            }

            // Helper function to format dates
            function formatDate(dateString) {
                if (!dateString || dateString === '0000-00-00') {
                    return '<span class="empty-value">Not provided</span>';
                }
                try {
                    return new Date(dateString).toLocaleDateString();
                } catch (e) {
                    return dateString;
                }
            }

            // Generate functions for different record types
            function generateDocumentDetails(button) {
                const title = button.getAttribute('data-record-title');
                const description = button.getAttribute('data-record-description');
                const filePath = button.getAttribute('data-record-file-path');
                const uploadedAt = button.getAttribute('data-record-uploaded-at');

                return `
                    <table class="details-modal-table">
                        <tr>
                            <th>Document Title:</th>
                            <td>${formatValue(title)}</td>
                        </tr>
                        <tr>
                            <th>Description:</th>
                            <td>${formatValue(description)}</td>
                        </tr>
                        <tr>
                            <th>File Path:</th>
                            <td>${formatValue(filePath)}</td>
                        </tr>
                        <tr>
                            <th>Uploaded Date:</th>
                            <td>${formatDate(uploadedAt)}</td>
                        </tr>
                    </table>
                `;
            }

            function generateAircraftCompetencyDetails(button) {
                const svcNo = button.getAttribute('data-record-svc-no');
                const rank = button.getAttribute('data-record-rank');
                const name = button.getAttribute('data-record-name');
                const trade = button.getAttribute('data-record-trade');
                const formation = button.getAttribute('data-record-formation');
                const postedInDate = button.getAttribute('data-record-posted-in-date');
                const postedOutDate = button.getAttribute('data-record-posted-out-date');
                const aircraftType = button.getAttribute('data-record-aircraft-type');
                const competencyLevel = button.getAttribute('data-record-competency-level');
                const trainingStartDate = button.getAttribute('data-record-training-start-date');
                const trainingEndDate = button.getAttribute('data-record-training-end-date');
                const formationRef = button.getAttribute('data-record-formation-ref');
                const forRefDate = button.getAttribute('data-record-for-ref-date');
                const qaiRef = button.getAttribute('data-record-qai-ref');
                const qaiRefDate = button.getAttribute('data-record-qai-ref-date');
                const dtRef = button.getAttribute('data-record-dt-ref');
                const dtRefDate = button.getAttribute('data-record-dt-ref-date');
                const qaoRef = button.getAttribute('data-record-qao-ref');
                const qaoRefDate = button.getAttribute('data-record-qao-ref-date');
                const theoryMarks = button.getAttribute('data-record-theory-marks');
                const practicalMarks = button.getAttribute('data-record-practical-marks');
                const competencyIssueRef = button.getAttribute('data-record-competency-issue-ref');
                const comIssueDate = button.getAttribute('data-record-com-issue-date');
                const competencyRenewRef = button.getAttribute('data-record-competency-renew-ref');
                const renewDate = button.getAttribute('data-record-renew-date');
                const certificateNo = button.getAttribute('data-record-certificate-no');
                const cerIssuedDate = button.getAttribute('data-record-cer-issued-date');
                const retiredDate = button.getAttribute('data-record-retired-date');
                const remarks = button.getAttribute('data-record-remarks');

                return `
                    <div class="section-divider">Personal Information</div>
                    <table class="details-modal-table">
                        <tr>
                            <th>SVC Number:</th>
                            <td>${formatValue(svcNo)}</td>
                        </tr>
                        <tr>
                            <th>Rank:</th>
                            <td>${formatValue(rank)}</td>
                        </tr>
                        <tr>
                            <th>Name:</th>
                            <td>${formatValue(name)}</td>
                        </tr>
                        <tr>
                            <th>Trade:</th>
                            <td>${formatValue(trade)}</td>
                        </tr>
                        <tr>
                            <th>Formation:</th>
                            <td>${formatValue(formation)}</td>
                        </tr>
                    </table>

                    <div class="section-divider">Posting Information</div>
                    <table class="details-modal-table">
                        <tr>
                            <th>Posted In Date:</th>
                            <td>${formatDate(postedInDate)}</td>
                        </tr>
                        <tr style="display: none;">
                            <th>Posted Out Date:</th>
                            <td>${formatDate(postedOutDate)}</td>
                        </tr>
                    </table>

                    <div class="section-divider">Aircraft & Competency Information</div>
                    <table class="details-modal-table">
                        <tr>
                            <th>Aircraft Type:</th>
                            <td>${formatValue(aircraftType)}</td>
                        </tr>
                        <tr>
                            <th>Competency Level:</th>
                            <td>${formatValue(competencyLevel)}</td>
                        </tr>
                        <tr>
                            <th>Training Start Date:</th>
                            <td>${formatDate(trainingStartDate)}</td>
                        </tr>
                        <tr>
                            <th>Training End Date:</th>
                            <td>${formatDate(trainingEndDate)}</td>
                        </tr>
                    </table>

                    <div class="section-divider">Reference Information</div>
                    <table class="details-modal-table">
                        <tr>
                            <th>Formation Reference:</th>
                            <td>${formatValue(formationRef)}</td>
                        </tr>
                        <tr>
                            <th>Formation Ref Date:</th>
                            <td>${formatDate(forRefDate)}</td>
                        </tr>
                        <tr>
                            <th>QAI Reference:</th>
                            <td>${formatValue(qaiRef)}</td>
                        </tr>
                        <tr>
                            <th>QAI Ref Date:</th>
                            <td>${formatDate(qaiRefDate)}</td>
                        </tr>
                        <tr>
                            <th>DT Reference:</th>
                            <td>${formatValue(dtRef)}</td>
                        </tr>
                        <tr>
                            <th>DT Ref Date:</th>
                            <td>${formatDate(dtRefDate)}</td>
                        </tr>
                        <tr>
                            <th>QAO Reference:</th>
                            <td>${formatValue(qaoRef)}</td>
                        </tr>
                        <tr>
                            <th>QAO Ref Date:</th>
                            <td>${formatDate(qaoRefDate)}</td>
                        </tr>
                    </table>

                    <div class="section-divider">Assessment Information</div>
                    <table class="details-modal-table">
                        <tr>
                            <th>Theory Marks:</th>
                            <td>${formatValue(theoryMarks)}</td>
                        </tr>
                        <tr>
                            <th>Practical Marks:</th>
                            <td>${formatValue(practicalMarks)}</td>
                        </tr>
                        <tr>
                            <th>Competency Issue Reference:</th>
                            <td>${formatValue(competencyIssueRef)}</td>
                        </tr>
                        <tr>
                            <th>Competency Issue Date:</th>
                            <td>${formatDate(comIssueDate)}</td>
                        </tr>
                        <tr>
                            <th>Competency Renew Reference:</th>
                            <td>${formatValue(competencyRenewRef)}</td>
                        </tr>
                        <tr>
                            <th>Renew Date:</th>
                            <td>${formatDate(renewDate)}</td>
                        </tr>
                    </table>

                    <div class="section-divider">Certificate Information</div>
                    <table class="details-modal-table">
                        <tr>
                            <th>Certificate Number:</th>
                            <td>${formatValue(certificateNo)}</td>
                        </tr>
                        <tr>
                            <th>Certificate Issued Date:</th>
                            <td>${formatDate(cerIssuedDate)}</td>
                        </tr>
                        <tr>
                            <th>Retired Date:</th>
                            <td>${formatDate(retiredDate)}</td>
                        </tr>
                    </table>

                    <div class="section-divider">Additional Information</div>
                    <table class="details-modal-table">
                        <tr>
                            <th>Remarks:</th>
                            <td>${formatValue(remarks)}</td>
                        </tr>
                    </table>
                `;
            }

            function generateLatitudeDetails(button) {
                const active = button.getAttribute('data-record-active');
                const typeValue = button.getAttribute('data-record-type-value');
                const formation = button.getAttribute('data-record-formation');
                const aircraftType = button.getAttribute('data-record-aircraft-type');
                const tailNo = button.getAttribute('data-record-tail-no');
                const partNo = button.getAttribute('data-record-part-no');
                const description = button.getAttribute('data-record-description');
                const serialNo = button.getAttribute('data-record-serial-no');
                const reason = button.getAttribute('data-record-reason');
                const hrs = button.getAttribute('data-record-hrs');
                const ldgs = button.getAttribute('data-record-ldgs');
                const date = button.getAttribute('data-record-date');
                const presentLatitude = button.getAttribute('data-record-present-latitude');
                const dgaeAuthRef = button.getAttribute('data-record-dgae-auth-ref');
                const recommendation = button.getAttribute('data-record-recommendation');
                const authDate = button.getAttribute('data-record-auth-date');
                const latitudeExpiry = button.getAttribute('data-record-latitude-expiry');
                const totalPrevLatitude = button.getAttribute('data-record-total-prev-latitude');
                const demandRef = button.getAttribute('data-record-demand-ref');
                const status = button.getAttribute('data-record-status');

                const statusBadge = status === 'Approved' ? 'success' :
                    status === 'Pending' ? 'warning' :
                    status === 'Expired' ? 'danger' : 'secondary';

                const activeBadge = active === 'YES' ? 'success' : 'danger';

                return `
                    <div class="section-divider">Basic Information</div>
                    <table class="details-modal-table">
                        <tr style="display: none;">
                            <th>Active Status:</th>
                            <td><span class="badge badge-${activeBadge}">${active}</span></td>
                        </tr>
                        <tr>
                            <th>Type:</th>
                            <td>${formatValue(typeValue)}</td>
                        </tr>
                        <tr>
                            <th>Formation:</th>
                            <td>${formatValue(formation)}</td>
                        </tr>
                        <tr>
                            <th>Aircraft Type:</th>
                            <td>${formatValue(aircraftType)}</td>
                        </tr>
                        <tr>
                            <th>Tail Number:</th>
                            <td>${formatValue(tailNo)}</td>
                        </tr>
                        <tr>
                            <th>Part Number:</th>
                            <td>${formatValue(partNo)}</td>
                        </tr>
                        <tr>
                            <th>Recommendation:</th>
                            <td>${formatValue(recommendation)}</td>
                        </tr>
                    </table>

                    <div class="section-divider">Technical Details</div>
                    <table class="details-modal-table">
                        <tr>
                            <th>Description:</th>
                            <td>${formatValue(description)}</td>
                        </tr>
                        <tr>
                            <th>Serial Number:</th>
                            <td>${formatValue(serialNo)}</td>
                        </tr>
                        <tr style="display: none;">
                            <th>Reason:</th>
                            <td>${formatValue(reason)}</td>
                        </tr>
                        <tr style="display: none;">
                            <th>Hours:</th>
                            <td>${formatValue(hrs)}</td>
                        </tr>
                        <tr style="display: none;">
                            <th>Landings:</th>
                            <td>${formatValue(ldgs)}</td>
                        </tr>
                        <tr style="display: none;">
                            <th>Date:</th>
                            <td>${formatDate(date)}</td>
                        </tr>
                    </table>

                    <div class="section-divider">Latitude Information</div>
                    <table class="details-modal-table">
                        <tr>
                            <th>Present Latitude:</th>
                            <td><strong>${formatValue(presentLatitude)}</strong></td>
                        </tr>
                        <tr>
                            <th>Total Previous Latitude:</th>
                            <td>${formatValue(totalPrevLatitude)}</td>
                        </tr>
                        <tr style="display: none;">
                            <th>DGAE Authorization Reference:</th>
                            <td>${formatValue(dgaeAuthRef)}</td>
                        </tr>
                        <tr style="display: none;">
                            <th>Authorization Date:</th>
                            <td>${formatDate(authDate)}</td>
                        </tr>
                        <tr>
                            <th>Latitude Expiry Date:</th>
                            <td>${formatDate(latitudeExpiry)}</td>
                        </tr>
                    </table>

                    <div class="section-divider" style="display: none;">Status Information</div>
                    <table class="details-modal-table" style="display: none;">
                        <tr style="display: none;">
                            <th>Demand Reference:</th>
                            <td>${formatValue(demandRef)}</td>
                        </tr>
                        <tr style="display: none;">
                            <th>Status:</th>
                            <td><span class="badge badge-${statusBadge}">${formatValue(status)}</span></td>
                        </tr>
                    </table>
                `;
            }

            function generateVehicleEmissionDetails(button) {
                const serialNo = button.getAttribute('data-record-serial-no');
                const camp = button.getAttribute('data-record-camp');
                const vehicleNo = button.getAttribute('data-record-vehicle-no');
                const vehicleType = button.getAttribute('data-record-vehicle-type');
                const model = button.getAttribute('data-record-model');
                const testDate = button.getAttribute('data-record-test-date');
                const firstTest = button.getAttribute('data-record-first-test');
                const secondTest = button.getAttribute('data-record-second-test');
                const thirdTest = button.getAttribute('data-record-third-test');
                const average = button.getAttribute('data-record-average');
                const rpm2500Hc = button.getAttribute('data-record-rpm-2500-hc');
                const rpm2500Co = button.getAttribute('data-record-rpm-2500-co');
                const idleHc = button.getAttribute('data-record-idle-hc');
                const idleCo = button.getAttribute('data-record-idle-co');
                const status = button.getAttribute('data-record-status');
                const nextDueDate = button.getAttribute('data-record-next-due-date');
                const remarks = button.getAttribute('data-record-remarks');

                const statusBadge = status === 'Pass' ? 'success' :
                    status === 'Fail' ? 'danger' :
                    status === 'Not Suitable' ? 'warning' :
                    status === 'Serviceable Not Done' ? 'secondary' : 'secondary';

                // Determine fuel type based on test values
                const isDiesel = firstTest || secondTest || thirdTest || average;
                const isPetrol = rpm2500Hc || rpm2500Co || idleHc || idleCo;

                return `
                    <div class="section-divider">Basic Information</div>
                    <table class="details-modal-table">
                        <tr>
                            <th>S/No:</th>
                            <td>${formatValue(serialNo)}</td>
                        </tr>
                        <tr>
                            <th>Camp:</th>
                            <td>${formatValue(camp)}</td>
                        </tr>
                        <tr>
                            <th>Vehicle Number:</th>
                            <td><strong>${formatValue(vehicleNo)}</strong></td>
                        </tr>
                        <tr>
                            <th>Vehicle Type:</th>
                            <td>${formatValue(vehicleType)}</td>
                        </tr>
                        <tr>
                            <th>Model:</th>
                            <td>${formatValue(model)}</td>
                        </tr>
                        <tr>
                            <th>Test Date:</th>
                            <td>${formatDate(testDate)}</td>
                        </tr>
                    </table>

                    <div class="section-divider">Test Results</div>
                    <table class="details-modal-table">
                        ${isDiesel ? `
                        <tr>
                            <th>1st Test Result:</th>
                            <td>${formatValue(firstTest)}</td>
                        </tr>
                        <tr>
                            <th>2nd Test Result:</th>
                            <td>${formatValue(secondTest)}</td>
                        </tr>
                        <tr>
                            <th>3rd Test Result:</th>
                            <td>${formatValue(thirdTest)}</td>
                        </tr>
                        <tr>
                            <th>Average:</th>
                            <td><strong>${formatValue(average)}</strong></td>
                        </tr>
                        ` : ''}
                        ${isPetrol ? `
                        <tr>
                            <th>2500 RPM HC:</th>
                            <td>${formatValue(rpm2500Hc)}</td>
                        </tr>
                        <tr>
                            <th>2500 RPM CO:</th>
                            <td>${formatValue(rpm2500Co)}</td>
                        </tr>
                        <tr>
                            <th>Idle HC:</th>
                            <td>${formatValue(idleHc)}</td>
                        </tr>
                        <tr>
                            <th>Idle CO:</th>
                            <td>${formatValue(idleCo)}</td>
                        </tr>
                        ` : ''}
                    </table>

                    <div class="section-divider">Status Information</div>
                    <table class="details-modal-table">
                        <tr>
                            <th>Status:</th>
                            <td><span class="badge badge-${statusBadge}">${formatValue(status)}</span></td>
                        </tr>
                        <tr>
                            <th>Next Due Date:</th>
                            <td>${formatDate(nextDueDate)}</td>
                        </tr>
                    </table>

                    <div class="section-divider">Additional Information</div>
                    <table class="details-modal-table">
                        <tr>
                            <th>Remarks:</th>
                            <td>${formatValue(remarks)}</td>
                        </tr>
                    </table>
                `;
            }

            // Set initial active tab to welcome screen
            const welcomePane = document.querySelector('#welcome');
            if (welcomePane) {
                welcomePane.classList.add('show', 'active');
            }

            // Remove any active classes from navigation items initially
            document.querySelectorAll('.nav-link, .qa-dropdown-item').forEach(item => {
                item.classList.remove('active');
            });

            // Close all dropdown menus initially
            document.querySelectorAll('.qa-dropdown-menu').forEach(menu => {
                menu.classList.remove('show');
            });

            // Handle dropdown toggle for ALL dropdowns
            const dropdownToggles = document.querySelectorAll('.qa-dropdown-toggle');
            dropdownToggles.forEach(toggle => {
                toggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    const dropdownMenu = this.nextElementSibling;
                    if (!dropdownMenu) return;

                    // Toggle current dropdown - close if open, open if closed
                    const isCurrentlyOpen = dropdownMenu.classList.contains('show');

                    // Close all dropdowns first
                    dropdownToggles.forEach(otherToggle => {
                        const otherMenu = otherToggle.nextElementSibling;
                        if (otherMenu) {
                            otherMenu.classList.remove('show');
                        }
                    });

                    // If it wasn't open, open it now
                    if (!isCurrentlyOpen) {
                        dropdownMenu.classList.add('show');
                    }
                });
            });

            // Handle tab selection for main nav links (non-dropdown items)
            const mainNavLinks = document.querySelectorAll('.nav-link:not(.qa-dropdown-toggle)');
            mainNavLinks.forEach(item => {
                item.addEventListener('click', function(e) {
                    e.preventDefault();

                    // Remove active class from all nav items
                    document.querySelectorAll('.nav-link, .qa-dropdown-item').forEach(tab => {
                        tab.classList.remove('active');
                    });

                    // Remove active class from all dropdown toggles
                    document.querySelectorAll('.qa-dropdown-toggle').forEach(toggle => {
                        toggle.classList.remove('active');
                    });

                    // Add active class to clicked tab
                    this.classList.add('active');

                    // Show the target tab content and hide welcome screen
                    const targetId = this.getAttribute('data-bs-target');
                    const targetPane = document.querySelector(targetId);

                    // Hide all tab panes including welcome
                    document.querySelectorAll('.tab-pane').forEach(pane => {
                        pane.classList.remove('show', 'active');
                    });

                    // Show the selected tab pane
                    if (targetPane) {
                        targetPane.classList.add('show', 'active');
                    }

                    // Close all dropdowns when selecting main nav items
                    document.querySelectorAll('.qa-dropdown-menu').forEach(menu => {
                        menu.classList.remove('show');
                    });
                });
            });

            // Handle tab selection for dropdown items (sub-menu items)
            const dropdownItems = document.querySelectorAll('.qa-dropdown-item');
            dropdownItems.forEach(item => {
                item.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation(); // Prevent event from bubbling to document

                    // Remove active class from all nav items
                    document.querySelectorAll('.nav-link, .qa-dropdown-item').forEach(tab => {
                        tab.classList.remove('active');
                    });

                    // Remove active class from all dropdown toggles
                    document.querySelectorAll('.qa-dropdown-toggle').forEach(toggle => {
                        toggle.classList.remove('active');
                    });

                    // Add active class to clicked dropdown item
                    this.classList.add('active');

                    // If this is a dropdown item, activate the parent dropdown toggle and keep menu open
                    if (this.classList.contains('qa-dropdown-item')) {
                        const parentDropdown = this.closest('.qa-dropdown');
                        if (parentDropdown) {
                            const dropdownToggle = parentDropdown.querySelector('.qa-dropdown-toggle');
                            if (dropdownToggle) {
                                dropdownToggle.classList.add('active');
                                // Keep the dropdown menu open
                                const dropdownMenu = dropdownToggle.nextElementSibling;
                                if (dropdownMenu) {
                                    dropdownMenu.classList.add('show');
                                }
                            }
                        }
                    }

                    // Show the target tab content and hide welcome screen
                    const targetId = this.getAttribute('data-bs-target');
                    const targetPane = document.querySelector(targetId);

                    // Hide all tab panes including welcome
                    document.querySelectorAll('.tab-pane').forEach(pane => {
                        pane.classList.remove('show', 'active');
                    });

                    // Show the selected tab pane
                    if (targetPane) {
                        targetPane.classList.add('show', 'active');
                    }

                    // DON'T close the dropdown menu - keep it open for sub-menu items
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

            // Prevent dropdown from closing when clicking inside dropdown menu
            document.querySelectorAll('.qa-dropdown-menu').forEach(menu => {
                menu.addEventListener('click', function(e) {
                    e.stopPropagation();
                });
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