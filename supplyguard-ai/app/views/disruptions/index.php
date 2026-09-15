<?php /** @var array $data */ $disruptions = $data['disruptions']; $statusFilter = $data['statusFilter'] ?? ''; $typeIcons = ['weather'=>'cloud-showers-heavy','road_closure'=>'road-barrier','port_congestion'=>'ship','vehicle_breakdown'=>'car-burst','supply_disruption'=>'boxes-stacked']; ?>
<div class="page-header">
    <div><h2><i class="fas fa-triangle-exclamation me-2" style="color:var(--warning)"></i>Disruption Center</h2></div>
    <div style="display:flex;gap:8px;">
        <a href="<?= URL_ROOT ?>disruptions?status=active" class="<?= $statusFilter === 'active' ? 'btn-primary-custom' : 'btn-outline-custom' ?> btn-sm">Active</a>
        <a href="<?= URL_ROOT ?>disruptions?status=monitoring" class="<?= $statusFilter === 'monitoring' ? 'btn-primary-custom' : 'btn-outline-custom' ?> btn-sm">Monitoring</a>
        <a href="<?= URL_ROOT ?>disruptions?status=resolved" class="<?= $statusFilter === 'resolved' ? 'btn-primary-custom' : 'btn-outline-custom' ?> btn-sm">Resolved</a>
        <a href="<?= URL_ROOT ?>disruptions" class="btn-outline-custom btn-sm">All</a>
        <?php if (Auth::isAdmin()): ?>
        <a href="<?= URL_ROOT ?>disruptions/create" class="btn-primary-custom btn-sm"><i class="fas fa-plus"></i> Add</a>
        <?php endif; ?>
    </div>
</div>

<div class="row g-3">
<?php if (empty($disruptions)): ?>
    <div class="col-12"><div class="card"><div class="empty-state"><i class="fas fa-check-circle" style="color:var(--success)"></i><h4>No disruptions found</h4></div></div></div>
<?php endif; ?>
<?php foreach ($disruptions as $d): ?>
<div class="col-lg-6">
    <div class="disruption-card" onclick="window.location='<?= URL_ROOT ?>disruptions/show/<?= $d->id ?>'">
        <div class="disruption-header">
            <div style="display:flex;gap:12px;align-items:flex-start;">
                <div class="disruption-type-icon <?= $d->type ?>">
                    <i class="fas fa-<?= $typeIcons[$d->type] ?? 'exclamation' ?>"></i>
                </div>
                <div>
                    <h6 style="font-size:15px;font-weight:600;margin-bottom:4px;"><?= Validator::escape($d->title) ?></h6>
                    <div style="font-size:12px;color:var(--text-muted);">
                        <i class="fas fa-location-dot me-1"></i><?= Validator::escape($d->location) ?>
                        <span class="ms-3"><i class="fas fa-clock me-1"></i><?= date('M d, H:i', strtotime($d->start_time)) ?></span>
                    </div>
                </div>
            </div>
            <div style="text-align:right;">
                <span class="badge-risk <?= strtolower($d->severity) ?>"><?= $d->severity ?></span>
                <div class="mt-1"><span class="badge-status <?= $d->status ?>"><?= ucfirst($d->status) ?></span></div>
            </div>
        </div>
        <p style="font-size:13px;color:var(--text-secondary);margin:12px 0 0;line-height:1.5;"><?= Validator::escape(substr($d->description, 0, 150)) ?>...</p>
        <div style="margin-top:12px;display:flex;gap:8px;">
            <a href="<?= URL_ROOT ?>disruptions/show/<?= $d->id ?>" class="btn-primary-custom btn-sm"><i class="fas fa-magnifying-glass-chart"></i> Analyze Impact</a>
        </div>
    </div>
</div>
<?php endforeach; ?>
</div>
