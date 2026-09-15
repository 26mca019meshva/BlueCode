<?php /** @var array $data */ $v = $data['vehicle']; ?>
<div class="page-header"><h2><?= Validator::escape($v->vehicle_code) ?></h2><a href="<?= URL_ROOT ?>fleet" class="btn-outline-custom btn-sm"><i class="fas fa-arrow-left"></i> Back</a></div>
<div class="card"><div class="card-body"><div class="detail-grid">
    <div class="detail-item"><div class="label">Vehicle Code</div><div class="value"><?= Validator::escape($v->vehicle_code) ?></div></div>
    <div class="detail-item"><div class="label">Type</div><div class="value"><?= ucfirst(str_replace('_', ' ', $v->vehicle_type)) ?></div></div>
    <div class="detail-item"><div class="label">Carrier</div><div class="value"><?= Validator::escape($v->carrier) ?></div></div>
    <div class="detail-item"><div class="label">Location</div><div class="value"><?= Validator::escape($v->current_location) ?></div></div>
    <div class="detail-item"><div class="label">Capacity</div><div class="value"><?= $v->capacity_tons ?>T</div></div>
    <div class="detail-item"><div class="label">Status</div><div class="value"><span class="badge-status <?= $v->status ?>"><?= ucfirst($v->status) ?></span></div></div>
    <div class="detail-item"><div class="label">Utilization</div><div class="value"><?= $v->utilization_percentage ?>%</div></div>
    <div class="detail-item"><div class="label">Temp Capable</div><div class="value"><?= $v->temperature_capable ? 'Yes ❄️' : 'No' ?></div></div>
    <div class="detail-item"><div class="label">Fuel</div><div class="value"><?= $v->fuel_level ?>%</div></div>
</div></div></div>
