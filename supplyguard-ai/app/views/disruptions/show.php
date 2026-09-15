<?php /** @var array $data */ $d = $data['disruption']; $affectedShipments = $data['affectedShipments']; $impact = $data['impact']; $recommendations = $data['recommendations']; $typeIcons = ['weather'=>'cloud-showers-heavy','road_closure'=>'road-barrier','port_congestion'=>'ship','vehicle_breakdown'=>'car-burst','supply_disruption'=>'boxes-stacked']; ?>

<div class="page-header">
    <div><h2><i class="fas fa-<?= $typeIcons[$d->type] ?? 'exclamation' ?> me-2" style="color:var(--warning)"></i><?= Validator::escape($d->title) ?></h2>
        <div class="breadcrumb">Dashboard / Disruptions / <?= Validator::escape($d->title) ?></div></div>
    <a href="<?= URL_ROOT ?>disruptions" class="btn-outline-custom btn-sm"><i class="fas fa-arrow-left"></i> Back</a>
</div>

<!-- Impact Summary -->
<div class="impact-summary mb-4">
    <div class="impact-item">
        <div class="value" style="color:var(--danger);"><?= $impact['total_affected'] ?></div>
        <div class="label">Affected Shipments</div>
    </div>
    <div class="impact-item">
        <div class="value" style="color:var(--risk-high);"><?= $impact['high_risk'] + $impact['critical_risk'] ?></div>
        <div class="label">High/Critical Risk</div>
    </div>
    <div class="impact-item">
        <div class="value" style="color:var(--warning);"><?= $impact['cargo_value_formatted'] ?></div>
        <div class="label">Cargo Value at Risk</div>
    </div>
    <div class="impact-item">
        <div class="value"><?= $impact['estimated_delay'] ?></div>
        <div class="label">Estimated Delay</div>
    </div>
</div>

<div class="row g-3">
    <!-- Disruption Details -->
    <div class="col-lg-4">
        <div class="card" style="height:100%;">
            <div class="card-header"><h5>Disruption Details</h5></div>
            <div class="card-body">
                <div class="detail-item mb-2"><div class="label">Type</div><div class="value"><?= ucfirst(str_replace('_', ' ', $d->type)) ?></div></div>
                <div class="detail-item mb-2"><div class="label">Location</div><div class="value"><?= Validator::escape($d->location) ?></div></div>
                <div class="detail-item mb-2"><div class="label">Severity</div><div class="value"><span class="badge-risk <?= strtolower($d->severity) ?>"><?= $d->severity ?></span></div></div>
                <div class="detail-item mb-2"><div class="label">Status</div><div class="value"><span class="badge-status <?= $d->status ?>"><?= ucfirst($d->status) ?></span></div></div>
                <div class="detail-item mb-2"><div class="label">Started</div><div class="value"><?= date('M d, H:i', strtotime($d->start_time)) ?></div></div>
                <div class="detail-item mb-2"><div class="label">Estimated End</div><div class="value"><?= $d->estimated_end_time ? date('M d, H:i', strtotime($d->estimated_end_time)) : 'Unknown' ?></div></div>
                <div class="detail-item mb-2"><div class="label">Affected Routes</div><div class="value" style="font-size:12px;"><?= Validator::escape($d->affected_routes) ?></div></div>
                <div class="detail-item"><div class="label">Description</div><div class="value" style="font-size:13px;line-height:1.5;"><?= Validator::escape($d->description) ?></div></div>
            </div>
        </div>
    </div>

    <!-- Actions & Affected -->
    <div class="col-lg-8">
        <!-- Priority Actions -->
        <?php if (!empty($impact['actions'])): ?>
        <div class="card mb-3">
            <div class="card-header"><h5><i class="fas fa-bolt me-2" style="color:var(--primary-light);"></i>Priority Actions</h5></div>
            <div class="card-body">
                <?php foreach ($impact['actions'] as $action): ?>
                <div style="display:flex;gap:12px;align-items:center;padding:10px;border-bottom:1px solid var(--border-color);">
                    <span class="badge-risk <?= strtolower($action['priority']) ?>"><?= $action['priority'] ?></span>
                    <div style="font-size:13px;color:var(--text-secondary);"><i class="fas fa-<?= $action['icon'] ?> me-2" style="color:var(--primary-light);"></i><?= Validator::escape($action['action']) ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Affected Shipments -->
        <div class="card">
            <div class="card-header"><h5><i class="fas fa-truck me-2" style="color:var(--danger);"></i>Affected Shipments (<?= count($affectedShipments) ?>)</h5></div>
            <div class="table-container">
                <table class="data-table">
                    <thead><tr><th>Code</th><th>Route</th><th>Cargo</th><th>Value</th><th>Risk</th><th>Status</th><th>Actions</th></tr></thead>
                    <tbody>
                    <?php foreach ($affectedShipments as $s): ?>
                    <tr>
                        <td><a href="<?= URL_ROOT ?>shipments/show/<?= $s->id ?>" style="font-weight:600;color:var(--primary-light);"><?= Validator::escape($s->shipment_code) ?></a></td>
                        <td style="font-size:12px;"><?= Validator::escape($s->origin) ?> → <?= Validator::escape($s->destination) ?></td>
                        <td style="font-size:12px;"><?= ucfirst(str_replace('_', ' ', $s->cargo_type)) ?></td>
                        <td style="font-size:12px;">₹<?= number_format($s->cargo_value) ?></td>
                        <td><span class="badge-risk <?= strtolower($s->risk_level) ?>"><?= $s->risk_level ?></span></td>
                        <td><span class="badge-status <?= $s->status ?>"><?= ucfirst(str_replace('_', ' ', $s->status)) ?></span></td>
                        <td><a href="<?= URL_ROOT ?>shipments/show/<?= $s->id ?>" class="btn-outline-custom btn-sm"><i class="fas fa-eye"></i></a></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Recommendations -->
<?php if (!empty($recommendations)): ?>
<div class="row g-3 mt-1">
    <div class="col-12"><div class="card">
        <div class="card-header"><h5><i class="fas fa-lightbulb me-2" style="color:var(--warning);"></i>AI Recommendations</h5></div>
        <div class="card-body">
            <?php foreach ($recommendations as $rec): ?>
            <div class="rec-card">
                <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
                    <span class="badge-risk <?= strtolower($rec->priority) ?>"><?= strtoupper($rec->priority) ?> — <?= ucfirst(str_replace('_', ' ', $rec->recommendation_type)) ?></span>
                    <span style="font-size:11px;color:var(--text-muted);"><?= Validator::escape($rec->shipment_code ?? '') ?></span>
                </div>
                <div class="rec-text"><?= Validator::escape($rec->recommendation_text) ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div></div>
</div>
<?php endif; ?>
