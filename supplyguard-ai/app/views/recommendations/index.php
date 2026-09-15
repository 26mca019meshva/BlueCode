<?php /** @var array $data */ $recommendations = $data['recommendations']; ?>
<div class="page-header">
    <div><h2><i class="fas fa-lightbulb me-2" style="color:var(--warning)"></i>AI Recommendations</h2>
        <div class="breadcrumb">AI-generated actionable recommendations based on disruptions, risk analysis, and fleet optimization</div></div>
</div>

<div class="card">
    <div class="table-container">
        <table class="data-table">
            <thead><tr><th>Priority</th><th>Type</th><th>Shipment</th><th>Disruption</th><th>Vehicle</th><th>Recommendation</th><th>Delay Reduction</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            <?php if (empty($recommendations)): ?>
                <tr><td colspan="9"><div class="empty-state"><i class="fas fa-lightbulb"></i><h4>No recommendations</h4></div></td></tr>
            <?php endif; ?>
            <?php foreach ($recommendations as $rec): ?>
            <tr>
                <td><span class="badge-risk <?= strtolower($rec->priority) ?>"><?= strtoupper($rec->priority) ?></span></td>
                <td style="font-size:12px;"><?= ucfirst(str_replace('_', ' ', $rec->recommendation_type)) ?></td>
                <td><?php if ($rec->shipment_code): ?><a href="<?= URL_ROOT ?>shipments/show/<?= $rec->shipment_id ?>" style="font-weight:600;color:var(--primary-light);"><?= Validator::escape($rec->shipment_code) ?></a><?php else: ?>—<?php endif; ?></td>
                <td style="font-size:12px;"><?= $rec->disruption_title ? Validator::escape(substr($rec->disruption_title, 0, 30)) . '...' : '—' ?></td>
                <td><?= $rec->vehicle_code ? Validator::escape($rec->vehicle_code) : '—' ?></td>
                <td style="font-size:12px;max-width:300px;"><?= Validator::escape(substr($rec->recommendation_text, 0, 100)) ?>...</td>
                <td style="font-size:12px;"><?= Validator::escape($rec->estimated_delay_reduction ?? 'N/A') ?></td>
                <td><span class="badge-status <?= $rec->status ?>"><?= ucfirst($rec->status) ?></span></td>
                <td>
                    <?php if ($rec->status === 'pending'): ?>
                    <div style="display:flex;gap:4px;">
                        <a href="<?= URL_ROOT ?>recommendations/apply/<?= $rec->id ?>" class="btn-primary-custom btn-sm" title="Accept"><i class="fas fa-check"></i></a>
                        <a href="<?= URL_ROOT ?>recommendations/reject/<?= $rec->id ?>" class="btn-outline-custom btn-sm" title="Reject"><i class="fas fa-times"></i></a>
                    </div>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
