// src/Views/incidents/index.php
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Gestion des incidents</h1>
        <a href="/incidents/create" class="btn btn-primary">
            <i class="fas fa-plus"></i> Signaler un incident
        </a>
    </div>
    
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Salle</th>
                            <th>Description</th>
                            <th>Signalé par</th>
                            <th>Date</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($incidents as $incident): ?>
                            <tr>
                                <td><?= htmlspecialchars($incident->getRoom()->getName()) ?></td>
                                <td><?= htmlspecialchars($incident->getDescription()) ?></td>
                                <td><?= htmlspecialchars($incident->getReporter()->getUsername()) ?></td>
                                <td><?= $incident->getFormattedCreatedAt() ?></td>
                                <td>
                                    <span class="badge bg-<?= $incident->getStatusColor() ?>">
                                        <?= $incident->getStatusLabel() ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="/incidents/edit/<?= $incident->getId() ?>" 
                                       class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>