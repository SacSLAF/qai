<?php
require_once '../includes/config.php';
// Get counts from database
$competency_count = 0;
$audit_count = 0;
$latitude_count = 0;
$extension_count = 0;

// Aircraft Competency Count
$res = $db->query("SELECT COUNT(*) as count FROM aircraft_competency");
if ($res) {
    $competency_count = $res->fetch_assoc()['count'];
}

// QA Audit Reports Count (qa_category_id 1 or 2)
$res = $db->query("SELECT COUNT(*) as count FROM service_documents WHERE qa_category_id = 1 AND is_active = 1");
if ($res) {
    $audit_count = $res->fetch_assoc()['count'];
}

// Latitude Records Count
$res = $db->query("SELECT COUNT(*) as count FROM latitude WHERE active = 'YES'");
if ($res) {
    $latitude_count = $res->fetch_assoc()['count'];
}

// Extension Records Count
$res = $db->query("SELECT COUNT(*) as count FROM service_documents WHERE qa_category_id = 2 AND is_active = 1");
if ($res) {
    $extension_count = $res->fetch_assoc()['count'];
}
?>

<div class="col-xl col-md-6">
    <div class="card">
        <div class="card-body p-4">
            <div class="d-inline-block mb-4 ms--12 position-relative donut-chart-sale">
                <span class="donut1" data-peity='{ "fill": ["rgb(192, 255, 134)", "rgba(255, 255, 255, 1)"],   "innerRadius": 45, "radius": 10}'>4/8</span>
                <small class="text-primary">
                    <svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <!-- Aircraft Competency Icon - Pilot/Engineer with Checklist -->
                        <path d="M20 5L5 15L20 25L35 15L20 5Z" stroke="white" stroke-width="2" fill="none"/>
                        <path d="M15 15L25 15" stroke="white" stroke-width="2"/>
                        <path d="M10 20L30 20" stroke="white" stroke-width="2"/>
                        <circle cx="20" cy="12" r="1" fill="white"/>
                        <path d="M18 28V35H22V28" stroke="white" stroke-width="2"/>
                    </svg>
                </small>
                <span class="circle bg-primary"></span>
            </div>
            <h2 class="fs-24 text-black font-w600 mb-0"><?= $competency_count ?></h2>
            <span class="fs-14">Aircraft Competency</span>
        </div>
    </div>
</div>

<div class="col-xl col-md-6 col-sm-6">
    <div class="card">
        <div class="card-body p-4">
            <div class="d-inline-block mb-4 ms--12 position-relative donut-chart-sale">
                <span class="donut1" data-peity='{ "fill": ["rgb(255, 195, 210)", "rgba(255, 255, 255, 1)"],   "innerRadius": 45, "radius": 10}'>3/8</span>
                <small class="text-primary">
                    <svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <!-- QA Audit Reports Icon - Clipboard with Checkmark -->
                        <rect x="12" y="8" width="16" height="20" rx="2" stroke="white" stroke-width="2" fill="none"/>
                        <path d="M12 12H28" stroke="white" stroke-width="2"/>
                        <path d="M12 16H28" stroke="white" stroke-width="2"/>
                        <path d="M12 20H20" stroke="white" stroke-width="2"/>
                        <path d="M16 24L18 26L22 22" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M15 8V6C15 5.44772 15.4477 5 16 5H24C24.5523 5 25 5.44772 25 6V8" stroke="white" stroke-width="2"/>
                    </svg>
                </small>
                <span class="circle bg-danger"></span>
            </div>
            <h2 class="fs-24 text-black font-w600 mb-0"><?= $audit_count ?></h2>
            <span class="fs-14">QA Audit Reports</span>
        </div>
    </div>
</div>

<div class="col-xl col-md-4 col-sm-6">
    <div class="card">
        <div class="card-body p-4">
            <div class="d-inline-block mb-4 ms--12 position-relative donut-chart-sale">
                <span class="donut1" data-peity='{ "fill": ["rgb(255, 213, 174)", "rgba(255, 255, 255, 1)"],   "innerRadius": 45, "radius": 10}'>5/8</span>
                <small class="text-primary">
                    <svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <!-- Latitude Records Icon - Gauge/Dial Indicator -->
                        <circle cx="20" cy="20" r="15" stroke="white" stroke-width="2" fill="none"/>
                        <circle cx="20" cy="20" r="2" fill="white"/>
                        <path d="M20 5V8" stroke="white" stroke-width="2"/>
                        <path d="M20 32V35" stroke="white" stroke-width="2"/>
                        <path d="M5 20H8" stroke="white" stroke-width="2"/>
                        <path d="M32 20H35" stroke="white" stroke-width="2"/>
                        <path d="M20 5L25 25" stroke="white" stroke-width="2" stroke-linecap="round"/>
                        <path d="M12 12L15 15" stroke="white" stroke-width="1"/>
                        <path d="M28 12L25 15" stroke="white" stroke-width="1"/>
                    </svg>
                </small>
                <span class="circle bg-warning"></span>
            </div>
            <h2 class="fs-24 text-black font-w600 mb-0"><?= $latitude_count ?></h2>
            <span class="fs-14">Latitude Records</span>
        </div>
    </div>
</div>

<div class="col-xl col-md-4 col-sm-6">
    <div class="card">
        <div class="card-body p-4">
            <div class="d-inline-block mb-4 ms--12 position-relative donut-chart-sale">
                <span class="donut1" data-peity='{ "fill": ["rgb(238, 252, 255)", "rgba(255, 255, 255, 1)"],   "innerRadius": 45, "radius": 10}'>8/8</span>
                <small class="text-primary">
                    <svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <!-- Extension Records Icon - Calendar with Plus -->
                        <rect x="8" y="10" width="24" height="20" rx="2" stroke="white" stroke-width="2" fill="none"/>
                        <path d="M8 15H32" stroke="white" stroke-width="2"/>
                        <path d="M15 8V12" stroke="white" stroke-width="2"/>
                        <path d="M25 8V12" stroke="white" stroke-width="2"/>
                        <path d="M12 20H16" stroke="white" stroke-width="1.5"/>
                        <path d="M20 20H24" stroke="white" stroke-width="1.5"/>
                        <path d="M28 20H32" stroke="white" stroke-width="1.5"/>
                        <path d="M12 25H16" stroke="white" stroke-width="1.5"/>
                        <path d="M20 25H24" stroke="white" stroke-width="1.5"/>
                        <path d="M28 25H32" stroke="white" stroke-width="1.5"/>
                        <path d="M20 30H24" stroke="white" stroke-width="1.5"/>
                        <path d="M28 30H32" stroke="white" stroke-width="1.5"/>
                        <path d="M35 18L35 22" stroke="white" stroke-width="2"/>
                        <path d="M33 20L37 20" stroke="white" stroke-width="2"/>
                    </svg>
                </small>
                <span class="circle bg-info"></span>
            </div>
            <h2 class="fs-24 text-black font-w600 mb-0"><?= $extension_count ?></h2>
            <span class="fs-14">Extension Records</span>
        </div>
    </div>
</div>