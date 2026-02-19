@extends('layouts.operator')

@section('title', 'Mes Adhérents')

@section('page-title', 'Gestion des Adhérents')

@push('styles')
<style>
    :root {
        --g-green: #009e3f;
        --g-green-light: #e6f7ed;
        --g-yellow: #ffcd00;
        --g-yellow-light: #fff8e0;
        --g-blue: #003f7f;
        --g-blue-light: #e8eff7;
    }

    /* Header */
    .page-header {
        background: var(--g-green);
        border-radius: 8px;
        padding: 1.25rem 1.5rem;
        margin-bottom: 1.5rem;
        color: #fff;
    }
    .page-header h4 {
        color: #fff;
        font-weight: 700;
        margin: 0;
        font-size: 1.25rem;
    }
    .page-header p {
        color: rgba(255,255,255,.75);
        margin: .2rem 0 0;
        font-size: 0.9rem;
    }

    /* Stats */
    .stat-row {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    @media (max-width: 768px) {
        .stat-row { grid-template-columns: repeat(2, 1fr); }
    }
    .stat-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 1.15rem 1.25rem;
        border-left: 4px solid transparent;
    }
    .stat-card:nth-child(1) { border-left-color: var(--g-green); }
    .stat-card:nth-child(2) { border-left-color: var(--g-yellow); }
    .stat-card:nth-child(3) { border-left-color: var(--g-blue); }
    .stat-card:nth-child(4) { border-left-color: #6b7280; }
    .stat-card .stat-value {
        font-size: 1.8rem;
        font-weight: 700;
        color: #1e293b;
        line-height: 1;
    }
    .stat-card .stat-label {
        font-size: 0.8rem;
        color: #64748b;
        font-weight: 500;
        margin-top: 0.35rem;
    }
    .stat-card .stat-icon {
        width: 42px;
        height: 42px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
    }
    .stat-card:nth-child(1) .stat-icon { background: var(--g-green-light); color: var(--g-green); }
    .stat-card:nth-child(2) .stat-icon { background: var(--g-yellow-light); color: #b8930a; }
    .stat-card:nth-child(3) .stat-icon { background: var(--g-blue-light); color: var(--g-blue); }
    .stat-card:nth-child(4) .stat-icon { background: #f1f5f9; color: #64748b; }

    /* Search */
    .search-bar {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 0.7rem 1rem;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    .search-bar i { color: #94a3b8; font-size: 0.95rem; }
    .search-bar input {
        background: transparent;
        border: none;
        color: #1e293b;
        font-size: 0.9rem;
        width: 100%;
        outline: none;
    }
    .search-bar input::placeholder { color: #94a3b8; }

    /* Org block */
    .org-block {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        margin-bottom: 1.25rem;
        overflow: hidden;
    }
    .org-block-header {
        padding: 1rem 1.25rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 2px solid var(--g-green-light);
        background: #fcfcfd;
    }
    .org-block-header .org-icon {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        background: var(--g-green);
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.85rem;
        flex-shrink: 0;
    }
    .org-block-header .org-name {
        color: #1e293b;
        font-size: 1rem;
        font-weight: 600;
        margin: 0;
    }
    .org-block-header .org-type {
        color: #64748b;
        font-size: 0.8rem;
        margin: 0;
    }
    .org-block-header .org-count {
        color: #fff;
        font-size: 0.78rem;
        font-weight: 600;
        background: var(--g-blue);
        padding: 0.3rem 0.8rem;
        border-radius: 20px;
    }

    /* Table */
    .data-table {
        width: 100%;
        border-collapse: collapse;
    }
    .data-table thead th {
        color: #fff;
        font-size: 0.78rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        padding: 0.7rem 1rem;
        background: var(--g-blue);
        border: none;
    }
    .data-table tbody td {
        color: #1e293b;
        font-size: 0.9rem;
        padding: 0.7rem 1rem;
        border-bottom: 1px solid #e2e8f0;
        vertical-align: middle;
    }
    .data-table tbody tr:nth-child(even) { background: #f8fafc; }
    .data-table tbody tr:hover { background: var(--g-green-light); }
    .data-table tbody tr:last-child td { border-bottom: none; }

    /* Avatar */
    .avatar-sm {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        background: var(--g-green);
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.7rem;
        font-weight: 700;
        flex-shrink: 0;
    }

    /* NIP */
    .nip-code {
        font-family: 'SFMono-Regular', Consolas, monospace;
        font-size: 0.82rem;
        color: var(--g-blue);
        background: var(--g-blue-light);
        padding: 0.15rem 0.45rem;
        border-radius: 4px;
        font-weight: 600;
    }

    /* Badges */
    .badge-active {
        background: var(--g-green-light);
        color: var(--g-green);
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.25rem 0.65rem;
        border-radius: 20px;
        border: 1px solid rgba(0,158,63,.2);
    }
    .badge-inactive {
        background: #fef2f2;
        color: #dc2626;
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.25rem 0.65rem;
        border-radius: 20px;
        border: 1px solid rgba(220,38,38,.15);
    }

    /* Action buttons (visibles) */
    .btn-action-view {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 6px;
        background: var(--g-blue-light);
        color: var(--g-blue);
        font-size: 0.8rem;
        border: 1px solid rgba(0,63,127,.15);
        transition: all 0.15s;
    }
    .btn-action-view:hover {
        background: var(--g-blue);
        color: #fff;
        text-decoration: none;
    }
    .btn-action-edit {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 6px;
        background: var(--g-yellow-light);
        color: #92710a;
        font-size: 0.8rem;
        border: 1px solid rgba(255,205,0,.35);
        transition: all 0.15s;
    }
    .btn-action-edit:hover {
        background: var(--g-yellow);
        color: #1e293b;
        text-decoration: none;
    }

    /* Footer */
    .org-block-footer {
        padding: 0.75rem 1.25rem;
        border-top: 1px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #fcfcfd;
    }
    .org-block-footer span { color: #64748b; font-size: 0.85rem; }
    .org-block-footer .btn-voir-tous {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        color: #fff;
        background: var(--g-green);
        font-size: 0.82rem;
        font-weight: 600;
        padding: 0.35rem 0.9rem;
        border-radius: 6px;
        transition: background 0.15s;
    }
    .org-block-footer .btn-voir-tous:hover { background: #007a32; color: #fff; text-decoration: none; }

    /* Empty */
    .empty-state {
        text-align: center;
        padding: 3rem 2rem;
    }
    .empty-state i {
        font-size: 2.5rem;
        color: #cbd5e1;
        margin-bottom: 1rem;
    }
    .empty-state h5 {
        color: #475569;
        font-weight: 600;
        font-size: 1rem;
        margin-bottom: 0.4rem;
    }
    .empty-state p {
        color: #94a3b8;
        font-size: 0.9rem;
        margin-bottom: 1.25rem;
    }

    /* Main buttons */
    .btn-gabon {
        background: var(--g-green);
        color: #fff;
        font-size: 0.85rem;
        font-weight: 600;
        padding: 0.5rem 1.15rem;
        border-radius: 6px;
        border: none;
        box-shadow: 0 1px 3px rgba(0,158,63,.25);
        transition: all 0.15s;
    }
    .btn-gabon:hover {
        background: #007a32;
        color: #fff;
        text-decoration: none;
        box-shadow: 0 2px 6px rgba(0,158,63,.35);
    }
    .btn-gabon-outline {
        background: #fff;
        color: var(--g-green);
        font-size: 0.85rem;
        font-weight: 600;
        padding: 0.5rem 1.15rem;
        border-radius: 6px;
        border: 2px solid var(--g-green);
        transition: all 0.15s;
    }
    .btn-gabon-outline:hover {
        background: var(--g-green);
        color: #fff;
        text-decoration: none;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">

    {{-- Header --}}
    <div class="page-header d-flex justify-content-between align-items-center">
        <div>
            <h4><i class="fas fa-users mr-2"></i>Adhérents</h4>
            <p>Vue globale de toutes vos organisations</p>
        </div>
        <a href="{{ route('operator.dossiers.create', 'association') }}" class="btn-gabon-outline" style="color:#fff; border-color:rgba(255,255,255,.5);">
            <i class="fas fa-plus mr-1"></i>Nouvelle organisation
        </a>
    </div>

    {{-- Stats --}}
    <div class="stat-row">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stat-value">{{ $totalAdherents ?? 0 }}</div>
                    <div class="stat-label">Total adhérents</div>
                </div>
                <div class="stat-icon"><i class="fas fa-users"></i></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stat-value" style="color:var(--g-green);">{{ $adherentsActifs ?? 0 }}</div>
                    <div class="stat-label">Actifs</div>
                </div>
                <div class="stat-icon"><i class="fas fa-user-check"></i></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stat-value" style="color:var(--g-blue);">{{ $adherentsInactifs ?? 0 }}</div>
                    <div class="stat-label">Inactifs</div>
                </div>
                <div class="stat-icon"><i class="fas fa-user-times"></i></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stat-value">{{ $organisations->count() ?? 0 }}</div>
                    <div class="stat-label">Organisations</div>
                </div>
                <div class="stat-icon"><i class="fas fa-building"></i></div>
            </div>
        </div>
    </div>

    {{-- Search --}}
    <div class="search-bar">
        <i class="fas fa-search"></i>
        <input type="text" id="searchInput" placeholder="Rechercher par nom, NIP, organisation...">
    </div>

    {{-- Organisations --}}
    @if($organisations && $organisations->count() > 0)
        @foreach($organisations as $organisation)
        <div class="org-block" data-org-name="{{ strtolower($organisation->nom ?? '') }}">
            <div class="org-block-header">
                <div class="d-flex align-items-center">
                    <div class="org-icon mr-2"><i class="fas fa-building"></i></div>
                    <div>
                        <h6 class="org-name">{{ $organisation->nom ?? 'Organisation' }}</h6>
                        <div class="org-type">{{ ucfirst(str_replace('_', ' ', $organisation->type ?? '')) }}</div>
                    </div>
                </div>
                <span class="org-count">{{ $organisation->adherents_count ?? 0 }} membre{{ ($organisation->adherents_count ?? 0) > 1 ? 's' : '' }}</span>
            </div>

            @if($organisation->adherents && $organisation->adherents->count() > 0)
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Adhérent</th>
                            <th>NIP</th>
                            <th>Téléphone</th>
                            <th>Date adhésion</th>
                            <th>Statut</th>
                            <th style="text-align:center; width:100px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($organisation->adherents as $adherent)
                        <tr data-name="{{ strtolower(($adherent->nom ?? '') . ' ' . ($adherent->prenom ?? '')) }}" data-nip="{{ strtolower($adherent->nip ?? '') }}">
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm mr-2">
                                        {{ strtoupper(substr($adherent->nom ?? 'U', 0, 1)) }}{{ strtoupper(substr($adherent->prenom ?? '', 0, 1)) }}
                                    </div>
                                    <div>
                                        <div style="font-weight:600; color:#1e293b;">{{ $adherent->nom ?? '' }} {{ $adherent->prenom ?? '' }}</div>
                                        @if($adherent->email)
                                            <div style="color:#64748b; font-size:0.8rem;">{{ $adherent->email }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td><span class="nip-code">{{ $adherent->nip ?: 'N/A' }}</span></td>
                            <td style="color:#475569;">{{ $adherent->telephone ?? '-' }}</td>
                            <td style="color:#475569;">{{ $adherent->date_adhesion ? $adherent->date_adhesion->format('d/m/Y') : '-' }}</td>
                            <td>
                                @if($adherent->is_active ?? true)
                                    <span class="badge-active"><i class="fas fa-check-circle mr-1"></i>Actif</span>
                                @else
                                    <span class="badge-inactive"><i class="fas fa-times-circle mr-1"></i>Inactif</span>
                                @endif
                            </td>
                            <td style="text-align:center;">
                                <a href="{{ route('operator.adherents.show', [$organisation->id, $adherent]) }}" class="btn-action-view" title="Voir le profil"><i class="fas fa-eye"></i></a>
                                <a href="{{ route('operator.adherents.edit', [$organisation->id, $adherent]) }}" class="btn-action-edit ml-1" title="Modifier"><i class="fas fa-pen"></i></a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($organisation->adherents_count > 5)
            <div class="org-block-footer">
                <span><i class="fas fa-info-circle mr-1"></i>{{ $organisation->adherents_count - 5 }} autre(s) membre(s) non affiché(s)</span>
                <a href="{{ route('operator.adherents.index', $organisation->id) }}" class="btn-voir-tous">Voir tous <i class="fas fa-arrow-right"></i></a>
            </div>
            @else
            <div class="org-block-footer">
                <span></span>
                <a href="{{ route('operator.adherents.index', $organisation->id) }}" class="btn-voir-tous">Gérer les adhérents <i class="fas fa-arrow-right"></i></a>
            </div>
            @endif

            @else
            <div class="empty-state">
                <i class="fas fa-user-plus d-block"></i>
                <h5>Aucun adhérent enregistré</h5>
                <p>Ajoutez votre premier membre dans cette organisation.</p>
                <a href="{{ route('operator.adherents.create', $organisation->id) }}" class="btn-gabon"><i class="fas fa-plus mr-1"></i>Ajouter un adhérent</a>
            </div>
            @endif
        </div>
        @endforeach
    @else
        <div class="org-block">
            <div class="empty-state">
                <i class="fas fa-building d-block"></i>
                <h5>Aucune organisation</h5>
                <p>Créez une organisation pour commencer à gérer vos adhérents.</p>
                <a href="{{ route('operator.dossiers.create', 'association') }}" class="btn-gabon"><i class="fas fa-plus mr-1"></i>Créer une organisation</a>
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#searchInput').on('input', function() {
        var q = $(this).val().toLowerCase().trim();
        if (q === '') {
            $('.org-block').show();
            $('.data-table tbody tr').show();
            return;
        }
        $('.org-block').each(function() {
            var block = $(this);
            var orgMatch = (block.data('org-name') || '').indexOf(q) !== -1;
            var rowMatch = false;
            block.find('tbody tr').each(function() {
                var row = $(this);
                var name = row.data('name') || '';
                var nip = row.data('nip') || '';
                if (name.indexOf(q) !== -1 || nip.indexOf(q) !== -1 || orgMatch) {
                    row.show();
                    rowMatch = true;
                } else {
                    row.hide();
                }
            });
            block.toggle(rowMatch || orgMatch);
        });
    });
});
</script>
@endpush
