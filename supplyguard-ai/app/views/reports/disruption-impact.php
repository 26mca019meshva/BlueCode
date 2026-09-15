<?php /** @var array $data */ $impactData = $data['impactData']; ?>
<div class="page-header"><h2>Disruption Impact Report</h2><div><button onclick="window.print()" class="btn-outline-custom btn-sm"><i class="fas fa-print"></i> Print</button> <a href="<?= URL_ROOT ?>reports" class="btn-outline-custom btn-sm"><i class="fas fa-arrow-left"></i> Back</a></div></div>
<div class="card"><div class="table-container">
    <table class="data-table"><thead><tr><th>Disruption</th><th>Type</th><th>Location</th><th>Severity</th><th>Status</th><th>Affected Shipments</th><th>Cargo at Risk (₹)</th></tr></thead>
    <tbody>
    <?php foreach ($impactData as $item): $d = $item->disruption; ?>
    <tr>
        <td style="font-weight:600;"><?= Validator::escape($d->title) ?></td>
        <td><?= ucfirst(str_replace('_', ' ', $d->type)) ?></td>
        <td><?= Validator::escape($d->location) ?></td>
        <td><span class="badge-risk <?= strtolower($d->severity) ?>"><?= $d->severity ?></span></td>
        <td><span class="badge-status <?= $d->status ?>"><?= ucfirst($d->status) ?></span></td>
        <td style="font-weight:700;"><?= $item->affected_count ?></td>
        <td>₹<?= number_format($item->cargo_value) ?></td>
    </tr>
    <?php endforeach; ?>
    </tbody></table>
</div></div>
