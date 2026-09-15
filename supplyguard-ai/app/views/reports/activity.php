<?php /** @var array $data */ $logs = $data['logs']; ?>
<div class="page-header"><h2>Activity Log</h2><a href="<?= URL_ROOT ?>reports" class="btn-outline-custom btn-sm"><i class="fas fa-arrow-left"></i> Back</a></div>
<div class="card"><div class="table-container">
    <table class="data-table"><thead><tr><th>Time</th><th>User</th><th>Action</th><th>Module</th><th>Description</th><th>IP</th></tr></thead>
    <tbody>
    <?php foreach ($logs as $log): ?>
    <tr>
        <td style="font-size:12px;white-space:nowrap;"><?= date('M d, H:i', strtotime($log->created_at)) ?></td>
        <td style="font-weight:600;"><?= Validator::escape($log->user_name ?? 'System') ?></td>
        <td><span class="badge-status <?= $log->action ?>"><?= ucfirst($log->action) ?></span></td>
        <td style="font-size:12px;"><?= Validator::escape($log->module) ?></td>
        <td style="font-size:12px;"><?= Validator::escape($log->description) ?></td>
        <td style="font-size:11px;color:var(--text-muted);"><?= Validator::escape($log->ip_address ?? '') ?></td>
    </tr>
    <?php endforeach; ?>
    </tbody></table>
</div></div>
