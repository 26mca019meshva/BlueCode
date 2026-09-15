<?php /** @var array $data */ ?>
<div class="page-header"><h2><i class="fas fa-plus me-2" style="color:var(--success)"></i>Create Disruption</h2><a href="<?= URL_ROOT ?>disruptions" class="btn-outline-custom btn-sm"><i class="fas fa-arrow-left"></i> Back</a></div>
<div class="card"><div class="card-body">
<form action="<?= URL_ROOT ?>disruptions/store" method="POST">
    <?= Auth::csrfField() ?>
    <div class="row g-3">
        <div class="col-md-8"><label class="form-label">Title *</label><input type="text" name="title" class="form-control" required placeholder="Heavy Rainfall — Vadodara Region"></div>
        <div class="col-md-4"><label class="form-label">Type *</label>
            <select name="type" class="form-select" required>
                <option value="weather">Weather</option><option value="road_closure">Road Closure</option><option value="port_congestion">Port Congestion</option><option value="vehicle_breakdown">Vehicle Breakdown</option><option value="supply_disruption">Supply Disruption</option>
            </select></div>
        <div class="col-md-4"><label class="form-label">Location *</label><input type="text" name="location" class="form-control" required></div>
        <div class="col-md-4"><label class="form-label">Severity *</label>
            <select name="severity" class="form-select" required><option value="LOW">Low</option><option value="MEDIUM" selected>Medium</option><option value="HIGH">High</option><option value="CRITICAL">Critical</option></select></div>
        <div class="col-md-4"><label class="form-label">Status</label>
            <select name="status" class="form-select"><option value="active">Active</option><option value="monitoring">Monitoring</option></select></div>
        <div class="col-md-12"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="3"></textarea></div>
        <div class="col-md-6"><label class="form-label">Affected Routes</label><input type="text" name="affected_routes" class="form-control" placeholder="Mumbai-Vadodara-Ahmedabad"></div>
        <div class="col-md-3"><label class="form-label">Radius (km)</label><input type="number" name="radius_km" class="form-control" value="50"></div>
        <div class="col-md-3"><label class="form-label">Start Time *</label><input type="datetime-local" name="start_time" class="form-control" required></div>
        <div class="col-md-3"><label class="form-label">Estimated End</label><input type="datetime-local" name="estimated_end_time" class="form-control"></div>
        <div class="col-12 mt-3"><button type="submit" class="btn-primary-custom"><i class="fas fa-save"></i> Create Disruption</button></div>
    </div>
</form></div></div>
