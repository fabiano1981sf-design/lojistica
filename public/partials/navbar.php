<?php
// File: public/partials/navbar.php
// Requer auth.php já incluído na página
$user = getCurrentUser();
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container-fluid">
        <!-- Logo / Nome do Sistema -->
        <a class="navbar-brand fw-bold" href="dashboard.php">
            <i class="fas fa-truck me-2"></i> Logística Interna
        </a>

        <!-- Botão Mobile -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" 
                aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Conteúdo do Menu -->
        <div class="collapse navbar-collapse" id="navbarContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">

                <!-- Dashboard -->
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : '' ?>" 
                       href="dashboard.php">
                        <i class="fas fa-tachometer-alt me-1"></i> Dashboard
                    </a>
                </li>

                <!-- Despachos -->
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : '' ?>" 
                       href="index.php">
                        <i class="fas fa-clipboard-list me-1"></i> Despachos
                    </a>
                </li>

                <!-- Novo Despacho -->
                <?php if (hasPermission('criar_despacho')): ?>
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'despacho_criar.php' ? 'active' : '' ?>" 
                       href="despacho_criar.php">
                        <i class="fas fa-plus-circle me-1"></i> Novo Despacho
                    </a>
                </li>
                <?php endif; ?>

                <!-- Scanner -->
                <?php if (hasPermission('atualizar_rastreio')): ?>
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'atualizar_status.php' ? 'active' : '' ?>" 
                       href="atualizar_status.php">
                        <i class="fas fa-qrcode me-1"></i> Scanner
                    </a>
                </li>
                <?php endif; ?>

                <!-- Aparelhos -->
                <?php if (hasPermission('gerenciar_aparelhos')): ?>
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'aparelhos.php' ? 'active' : '' ?>" 
                       href="aparelhos.php">
                        <i class="fas fa-microchip me-1"></i> Aparelhos
                    </a>
                </li>
                <?php endif; ?>

                <!-- Relatórios -->
                <?php if (hasPermission('visualizar_relatorios')): ?>
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'relatorios.php' ? 'active' : '' ?>" 
                       href="relatorios.php">
                        <i class="fas fa-chart-bar me-1"></i> Relatórios
                    </a>
                </li>
                <?php endif; ?>

                <!-- CONFIGURAÇÕES (DROPDOWN) -->
                <?php if (hasPermission('gerenciar_configuracoes') || hasPermission('gerenciar_usuarios')): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?= in_array(basename($_SERVER['PHP_SELF']), ['configuracoes.php', 'usuarios.php']) ? 'active' : '' ?>" 
                       href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-cogs me-1"></i> Configurações
                    </a>
                    <ul class="dropdown-menu">
                        <?php if (hasPermission('gerenciar_configuracoes')): ?>
                        <li>
                            <a class="dropdown-item <?= basename($_SERVER['PHP_SELF']) === 'configuracoes.php' ? 'active' : '' ?>" 
                               href="configuracoes.php">
                                <i class="fas fa-truck me-2"></i> Transportadoras
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if (hasPermission('gerenciar_usuarios')): ?>
                        <li>
                            <a class="dropdown-item <?= basename($_SERVER['PHP_SELF']) === 'usuarios.php' ? 'active' : '' ?>" 
                               href="usuarios.php">
                                <i class="fas fa-users me-2"></i> Usuários
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </li>
                <?php endif; ?>

            </ul>

            <!-- Perfil do Usuário + Logout -->
            <div class="d-flex align-items-center">
                <div class="dropdown">
                    <a class="dropdown-toggle text-white text-decoration-none d-flex align-items-center" 
                       href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-user-circle fa-lg me-2"></i>
                        <span class="d-none d-sm-inline"><?= h($user['nome'] ?? 'Usuário') ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item <?= basename($_SERVER['PHP_SELF']) === 'perfil.php' ? 'active' : '' ?>" 
                               href="perfil.php">
                                <i class="fas fa-user me-2"></i> Meu Perfil
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger" href="logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i> Sair
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</nav>