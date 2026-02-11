<!DOCTYPE html>
<html ng-app="servicoSimples">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ServicoSimples - Controle de Ordens de Serviço</title>
    <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.8.2/angular.min.js"></script>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; color: #333; min-height: 100vh; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        header { background: #2c3e50; color: white; padding: 15px 20px; margin-bottom: 20px; }
        header h1 { display: inline-block; }
        .nav { float: right; display: flex; align-items: center; gap: 10px; }
        .nav button, .nav span { background: transparent; border: 1px solid white; color: white; padding: 8px 15px; margin-left: 10px; cursor: pointer; border-radius: 4px; text-decoration: none; font-size: 14px; display: inline-block; }
        .nav button.active, .nav button:hover { background: white; color: #2c3e50; }
        .nav .user-info { border: none; background: transparent; }
        .card { background: white; border-radius: 8px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .stats { display: flex; gap: 20px; flex-wrap: wrap; margin-bottom: 20px; }
        .stat { flex: 1; min-width: 150px; background: white; padding: 20px; border-radius: 8px; text-align: center; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .stat h3 { font-size: 2em; color: #2c3e50; }
        .stat p { color: #666; }
        .stat.green { border-top: 4px solid #27ae60; }
        .stat.blue { border-top: 4px solid #3498db; }
        .stat.orange { border-top: 4px solid #f39c12; }
        .stat.red { border-top: 4px solid #e74c3c; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f8f9fa; font-weight: bold; }
        .btn { padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer; }
        .btn-primary { background: #3498db; color: white; }
        .btn-success { background: #27ae60; color: white; }
        .btn-danger { background: #e74c3c; color: white; }
        .btn-secondary { background: #95a5a6; color: white; }
        .btn-sm { padding: 4px 8px; font-size: 12px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; }
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; }
        .modal.active { display: flex; align-items: center; justify-content: center; }
        .modal-content { background: white; padding: 30px; border-radius: 8px; max-width: 500px; width: 90%; max-height: 90vh; overflow-y: auto; }
        .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .modal-header h2 { margin: 0; }
        .close { background: none; border: none; font-size: 24px; cursor: pointer; color: #666; }
        .badge { padding: 4px 8px; border-radius: 4px; font-size: 12px; color: white; }
        .badge-pendente { background: #f39c12; }
        .badge-concluido { background: #3498db; }
        .badge-pago { background: #27ae60; }
        .search-box { padding: 10px; border: 1px solid #ddd; border-radius: 4px; width: 300px; }
        .toolbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .error { background: #e74c3c; color: white; padding: 10px; border-radius: 4px; margin-bottom: 10px; }
        .success { background: #27ae60; color: white; padding: 10px; border-radius: 4px; margin-bottom: 10px; }
        
        /* Auth Styles */
        .auth-container { display: flex; align-items: center; justify-content: center; min-height: calc(100vh - 100px); }
        .auth-box { background: white; padding: 40px; border-radius: 10px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        .auth-box h2 { text-align: center; margin-bottom: 10px; color: #2c3e50; }
        .auth-box p { text-align: center; color: #666; margin-bottom: 30px; }
        .auth-box .form-group { margin-bottom: 20px; }
        .auth-box .btn-primary { width: 100%; padding: 12px; font-size: 16px; }
        .auth-link { display: block; text-align: center; margin-top: 20px; color: #3498db; cursor: pointer; }
        .auth-link:hover { text-decoration: underline; }
        .text-center { text-align: center; }
    </style>
</head>
<body ng-controller="MainController">
    
    <!-- TELA DE AUTENTICAÇÃO -->
    <div ng-if="!isAuthenticated()" class="auth-container">
        <!-- Login -->
        <div class="auth-box" ng-if="authView == 'login'">
            <h2>🔧 ServicoSimples</h2>
            <p>Controle de Ordens de Serviço para MEIs</p>
            
            <div class="error" ng-if="authError" ng-bind="authError"></div>
            
            <form ng-submit="login()">
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" ng-model="auth.email" required placeholder="Seu email">
                </div>
                <div class="form-group">
                    <label>Senha</label>
                    <input type="password" ng-model="auth.password" required placeholder="Sua senha">
                </div>
                <button type="submit" class="btn btn-primary">Entrar</button>
            </form>
            
            <div class="auth-link" ng-click="setAuthView('register')">Não tem conta? Cadastre-se</div>
        </div>

        <!-- Registro -->
        <div class="auth-box" ng-if="authView == 'register'">
            <h2>🔧 ServicoSimples</h2>
            <p>Crie sua conta gratuita</p>
            
            <div class="error" ng-if="authError" ng-bind="authError"></div>
            
            <form ng-submit="register()">
                <div class="form-group">
                    <label>Nome Completo</label>
                    <input type="text" ng-model="registerData.name" required placeholder="Seu nome">
                </div>
                <div class="form-group">
                    <label>Nome da Empresa/MEI</label>
                    <input type="text" ng-model="registerData.empresa" placeholder="Ex: João Elétrica">
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" ng-model="registerData.email" required placeholder="Seu email">
                </div>
                <div class="form-group">
                    <label>Senha</label>
                    <input type="password" ng-model="registerData.password" required minlength="6" placeholder="Mínimo 6 caracteres">
                </div>
                <div class="form-group">
                    <label>Confirmar Senha</label>
                    <input type="password" ng-model="registerData.password_confirmation" required placeholder="Digite a senha novamente">
                </div>
                <button type="submit" class="btn btn-primary">Cadastrar</button>
            </form>
            
            <div class="auth-link" ng-click="setAuthView('login')">Já tem conta? Faça login</div>
        </div>
    </div>

    <!-- APLICAÇÃO PRINCIPAL -->
    <div ng-if="isAuthenticated()">
        <header>
            <h1>🔧 ServicoSimples</h1>
            <nav class="nav">
                <span class="user-info">👤 @{{ currentUser.name }}</span>
                <button ng-class="{active: view == 'dashboard'}" ng-click="view = 'dashboard'">Dashboard</button>
                <button ng-class="{active: view == 'ordens'}" ng-click="view = 'ordens'">Ordens de Serviço</button>
                <button ng-class="{active: view == 'clientes'}" ng-click="view = 'clientes'">Clientes</button>
                <button ng-class="{active: view == 'servicos'}" ng-click="view = 'servicos'">Serviços</button>
                <button ng-click="logout()">Sair</button>
            </nav>
        </header>

        <div class="container">
            <!-- DEBUG: Angular version @{{ 'Angular OK: ' + (1+1) }} -->
            <!-- Dashboard -->
            <div ng-if="view == 'dashboard'">
                <h2>Dashboard</h2>
                <br>
                <div class="stats">
                    <div class="stat">
                        <h3 ng-bind="dashboard.total">0</h3>
                        <p>Total de OS</p>
                    </div>
                    <div class="stat orange">
                        <h3 ng-bind="dashboard.pendente">0</h3>
                        <p>Pendentes</p>
                    </div>
                    <div class="stat blue">
                        <h3 ng-bind="dashboard.concluido">0</h3>
                        <p>Concluídos</p>
                    </div>
                    <div class="stat green">
                        <h3 ng-bind="dashboard.pago">0</h3>
                        <p>Pagos</p>
                    </div>
                </div>
                <div class="stats">
                    <div class="stat green">
                        <h3>R$ <span ng-bind="dashboard.faturamento_mes | number:2">0.00</span></h3>
                        <p>Faturamento do Mês</p>
                    </div>
                    <div class="stat">
                        <h3>R$ <span ng-bind="dashboard.faturamento_total | number:2">0.00</span></h3>
                        <p>Faturamento Total</p>
                    </div>
                </div>
            </div>

            <!-- Ordens de Serviço -->
            <div ng-if="view == 'ordens'">
                <div class="toolbar">
                    <h2>Ordens de Serviço</h2>
                    <button class="btn btn-primary" ng-click="openModalOS()">+ Nova OS</button>
                </div>
                <div class="card">
                    <input type="text" class="search-box" ng-model="searchOS" placeholder="Buscar ordens...">
                    <br><br>
                    <table>
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Cliente</th>
                                <th>Descrição</th>
                                <th>Valor</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr ng-repeat="os in ordemServicos | filter:searchOS">
                                <td ng-bind="os.data | date:'dd/MM/yyyy'"></td>
                                <td ng-bind="os.cliente.nome"></td>
                                <td ng-bind="os.descricao | limitTo:50"></td>
                                <td>R$ <span ng-bind="os.valor | number:2"></span></td>
                                <td><span class="badge badge-@{{os.status}}" ng-bind="os.status"></span></td>
                                <td>
                                    <button class="btn btn-success btn-sm" ng-click="updateStatus(os)" ng-if="os.status != 'pago'">Pagar</button>
                                    <button class="btn btn-primary btn-sm" ng-click="editOS(os)">Editar</button>
                                    <button class="btn btn-danger btn-sm" ng-click="deleteOS(os)">Excluir</button>
                                </td>
                            </tr>
                            <tr ng-if="ordemServicos.length === 0">
                                <td colspan="6" class="text-center">Nenhuma OS cadastrada</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Clientes -->
            <div ng-if="view == 'clientes'">
                <div class="toolbar">
                    <h2>Clientes</h2>
                    <button class="btn btn-primary" ng-click="openModalCliente()">+ Novo Cliente</button>
                </div>
                <div class="card">
                    <input type="text" class="search-box" ng-model="searchCliente" placeholder="Buscar clientes...">
                    <br><br>
                    <table>
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Email</th>
                                <th>Telefone</th>
                                <th>Endereço</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr ng-repeat="c in clientes | filter:searchCliente">
                                <td ng-bind="c.nome"></td>
                                <td ng-bind="c.email || '-'"></td>
                                <td ng-bind="c.telefone"></td>
                                <td ng-bind="c.endereco || '-'"></td>
                                <td>
                                    <button class="btn btn-primary btn-sm" ng-click="editCliente(c)">Editar</button>
                                    <button class="btn btn-danger btn-sm" ng-click="deleteCliente(c)">Excluir</button>
                                </td>
                            </tr>
                            <tr ng-if="clientes.length === 0">
                                <td colspan="5" class="text-center">Nenhum cliente cadastrado</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Serviços -->
            <div ng-if="view == 'servicos'">
                <div class="toolbar">
                    <h2>Serviços</h2>
                    <button class="btn btn-primary" ng-click="openModalServico()">+ Novo Serviço</button>
                </div>
                <div class="card">
                    <input type="text" class="search-box" ng-model="searchServico" placeholder="Buscar serviços...">
                    <br><br>
                    <table>
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Descrição</th>
                                <th>Valor Padrão</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr ng-repeat="s in servicos | filter:searchServico">
                                <td ng-bind="s.nome"></td>
                                <td ng-bind="s.descricao || '-'"></td>
                                <td>R$ <span ng-bind="s.valor_padrao | number:2"></span></td>
                                <td>
                                    <button class="btn btn-primary btn-sm" ng-click="editServico(s)">Editar</button>
                                    <button class="btn btn-danger btn-sm" ng-click="deleteServico(s)">Excluir</button>
                                </td>
                            </tr>
                            <tr ng-if="servicos.length === 0">
                                <td colspan="4" class="text-center">Nenhum serviço cadastrado</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal Cliente -->
        <div class="modal" ng-class="{active: showModalCliente}">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>@{{editandoCliente ? 'Editar' : 'Novo'}} Cliente</h2>
                    <button class="close" ng-click="showModalCliente = false">&times;</button>
                </div>
                <div class="error" ng-if="errorMsg" ng-bind="errorMsg"></div>
                <form ng-submit="salvarCliente()">
                    <div class="form-group">
                        <label>Nome *</label>
                        <input type="text" ng-model="cliente.nome" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" ng-model="cliente.email">
                    </div>
                    <div class="form-group">
                        <label>Telefone *</label>
                        <input type="text" ng-model="cliente.telefone" required>
                    </div>
                    <div class="form-group">
                        <label>Endereço</label>
                        <input type="text" ng-model="cliente.endereco">
                    </div>
                    <div class="form-group">
                        <label>Observações</label>
                        <textarea ng-model="cliente.observacoes" rows="3"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </form>
            </div>
        </div>

        <!-- Modal Serviço -->
        <div class="modal" ng-class="{active: showModalServico}">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>@{{editandoServico ? 'Editar' : 'Novo'}} Serviço</h2>
                    <button class="close" ng-click="showModalServico = false">&times;</button>
                </div>
                <div class="error" ng-if="errorMsg" ng-bind="errorMsg"></div>
                <form ng-submit="salvarServico()">
                    <div class="form-group">
                        <label>Nome *</label>
                        <input type="text" ng-model="servico.nome" required>
                    </div>
                    <div class="form-group">
                        <label>Descrição</label>
                        <textarea ng-model="servico.descricao" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Valor Padrão</label>
                        <input type="number" step="0.01" ng-model="servico.valor_padrao">
                    </div>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </form>
            </div>
        </div>

        <!-- Modal OS -->
        <div class="modal" ng-class="{active: showModalOS}">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>@{{editandoOS ? 'Editar' : 'Nova'}} Ordem de Serviço</h2>
                    <button class="close" ng-click="showModalOS = false">&times;</button>
                </div>
                <div class="error" ng-if="errorMsg" ng-bind="errorMsg"></div>
                <form ng-submit="salvarOS()">
                    <div class="form-group">
                        <label>Cliente *</label>
                        <select ng-model="ordemServico.cliente_id" required>
                            <option value="">Selecione um cliente</option>
                            <option ng-repeat="c in clientes" value="@{{c.id}}">@{{c.nome}}</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Data *</label>
                        <input type="date" ng-model="ordemServico.data" required>
                    </div>
                    <div class="form-group">
                        <label>Descrição *</label>
                        <textarea ng-model="ordemServico.descricao" rows="3" required></textarea>
                    </div>
                    <div class="form-group">
                        <label>Valor (R$) *</label>
                        <input type="number" step="0.01" ng-model="ordemServico.valor" required>
                    </div>
                    <div class="form-group" ng-if="editandoOS">
                        <label>Status</label>
                        <select ng-model="ordemServico.status">
                            <option value="pendente">Pendente</option>
                            <option value="concluido">Concluído</option>
                            <option value="pago">Pago</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Observações</label>
                        <textarea ng-model="ordemServico.observacoes" rows="2"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        var app = angular.module('servicoSimples', []);
        
        app.controller('MainController', function($scope, $http) {
            // Auth state
            $scope.authView = 'login';
            $scope.setAuthView = function(view) {
                $scope.authView = view;
                $scope.authError = '';
            };
            
            $scope.isAuthenticated = function() {
                return !!localStorage.getItem('auth_token');
            };
            $scope.currentUser = JSON.parse(localStorage.getItem('user') || '{}');
            
            $scope.view = 'dashboard';
            $scope.clientes = [];
            $scope.servicos = [];
            $scope.ordemServicos = [];
            $scope.dashboard = {};
            $scope.showModalCliente = false;
            $scope.showModalServico = false;
            $scope.showModalOS = false;
            $scope.editandoCliente = false;
            $scope.editandoServico = false;
            $scope.editandoOS = false;
            $scope.errorMsg = '';
            $scope.authError = '';
            
            $scope.cliente = {};
            $scope.servico = {};
            $scope.ordemServico = {};
            $scope.auth = {};
            $scope.registerData = {};
            
            var API = '/api';
            
            // HTTP interceptor for auth
            $http.defaults.headers.common['Authorization'] = 'Bearer ' + (localStorage.getItem('auth_token') || '');
            
            // Auth functions
            $scope.login = function() {
                $scope.authError = '';
                $http.post(API + '/auth/login', $scope.auth).then(function(res) {
                    localStorage.setItem('auth_token', res.data.data.token);
                    localStorage.setItem('user', JSON.stringify(res.data.data.user));
                    $http.defaults.headers.common['Authorization'] = 'Bearer ' + res.data.data.token;
                    $scope.currentUser = res.data.data.user;
                    $scope.auth = {};
                    loadAllData();
                }).catch(function(err) {
                    $scope.authError = err.data ? (err.data.message || 'Erro ao fazer login') : 'Erro de conexão';
                });
            };
            
            $scope.register = function() {
                $scope.authError = '';
                if ($scope.registerData.password !== $scope.registerData.password_confirmation) {
                    $scope.authError = 'As senhas não coincidem';
                    return;
                }
                $http.post(API + '/auth/register', $scope.registerData).then(function(res) {
                    localStorage.setItem('auth_token', res.data.data.token);
                    localStorage.setItem('user', JSON.stringify(res.data.data.user));
                    $http.defaults.headers.common['Authorization'] = 'Bearer ' + res.data.data.token;
                    $scope.currentUser = res.data.data.user;
                    $scope.registerData = {};
                    loadAllData();
                }).catch(function(err) {
                    if (err.data && err.data.errors) {
                        var msgs = [];
                        for (var key in err.data.errors) {
                            msgs.push(err.data.errors[key][0]);
                        }
                        $scope.authError = msgs.join(', ');
                    } else {
                        $scope.authError = err.data ? (err.data.message || 'Erro ao cadastrar') : 'Erro de conexão';
                    }
                });
            };
            
            $scope.logout = function() {
                $http.post(API + '/auth/logout').finally(function() {
                    localStorage.removeItem('auth_token');
                    localStorage.removeItem('user');
                    delete $http.defaults.headers.common['Authorization'];
                    $scope.currentUser = {};
                    $scope.clientes = [];
                    $scope.servicos = [];
                    $scope.ordemServicos = [];
                    $scope.dashboard = {};
                    $scope.setAuthView('login');
                });
            };
            
            // Load all data
            function loadAllData() {
                loadDashboard();
                loadClientes();
                loadServicos();
                loadOS();
            }
            
            function loadDashboard() {
                $http.get(API + '/dashboard').then(function(res) {
                    $scope.dashboard = res.data;
                });
            }
            
            function loadClientes() {
                $http.get(API + '/clientes').then(function(res) {
                    $scope.clientes = res.data;
                });
            }
            
            function loadServicos() {
                $http.get(API + '/servicos').then(function(res) {
                    $scope.servicos = res.data;
                });
            }
            
            function loadOS() {
                $http.get(API + '/ordem-servicos').then(function(res) {
                    $scope.ordemServicos = res.data;
                });
            }
            
            // Cliente functions
            $scope.openModalCliente = function() {
                $scope.cliente = {};
                $scope.editandoCliente = false;
                $scope.errorMsg = '';
                $scope.showModalCliente = true;
            };
            
            $scope.editCliente = function(c) {
                $scope.cliente = angular.copy(c);
                $scope.editandoCliente = true;
                $scope.errorMsg = '';
                $scope.showModalCliente = true;
            };
            
            $scope.salvarCliente = function() {
                var req = $scope.editandoCliente ? 
                    $http.put(API + '/clientes/' + $scope.cliente.id, $scope.cliente) :
                    $http.post(API + '/clientes', $scope.cliente);
                
                req.then(function() {
                    $scope.showModalCliente = false;
                    loadClientes();
                    loadOS();
                }).catch(function(err) {
                    if (err.data && err.data.errors) {
                        var msgs = [];
                        for (var key in err.data.errors) {
                            msgs.push(err.data.errors[key][0]);
                        }
                        $scope.errorMsg = msgs.join(', ');
                    } else {
                        $scope.errorMsg = 'Erro ao salvar';
                    }
                });
            };
            
            $scope.deleteCliente = function(c) {
                if(confirm('Excluir ' + c.nome + '?')) {
                    $http.delete(API + '/clientes/' + c.id).then(function() {
                        loadClientes();
                        loadOS();
                    });
                }
            };
            
            // Servico functions
            $scope.openModalServico = function() {
                $scope.servico = {};
                $scope.editandoServico = false;
                $scope.errorMsg = '';
                $scope.showModalServico = true;
            };
            
            $scope.editServico = function(s) {
                $scope.servico = angular.copy(s);
                $scope.editandoServico = true;
                $scope.errorMsg = '';
                $scope.showModalServico = true;
            };
            
            $scope.salvarServico = function() {
                var req = $scope.editandoServico ? 
                    $http.put(API + '/servicos/' + $scope.servico.id, $scope.servico) :
                    $http.post(API + '/servicos', $scope.servico);
                
                req.then(function() {
                    $scope.showModalServico = false;
                    loadServicos();
                }).catch(function(err) {
                    if (err.data && err.data.errors) {
                        var msgs = [];
                        for (var key in err.data.errors) {
                            msgs.push(err.data.errors[key][0]);
                        }
                        $scope.errorMsg = msgs.join(', ');
                    } else {
                        $scope.errorMsg = 'Erro ao salvar';
                    }
                });
            };
            
            $scope.deleteServico = function(s) {
                if(confirm('Excluir ' + s.nome + '?')) {
                    $http.delete(API + '/servicos/' + s.id).then(function() {
                        loadServicos();
                    });
                }
            };
            
            // OS functions
            $scope.openModalOS = function() {
                $scope.ordemServico = { status: 'pendente' };
                $scope.editandoOS = false;
                $scope.errorMsg = '';
                $scope.showModalOS = true;
            };
            
            $scope.editOS = function(os) {
                $scope.ordemServico = angular.copy(os);
                $scope.editandoOS = true;
                $scope.errorMsg = '';
                $scope.showModalOS = true;
            };
            
            $scope.salvarOS = function() {
                var req = $scope.editandoOS ? 
                    $http.put(API + '/ordem-servicos/' + $scope.ordemServico.id, $scope.ordemServico) :
                    $http.post(API + '/ordem-servicos', $scope.ordemServico);
                
                req.then(function() {
                    $scope.showModalOS = false;
                    loadOS();
                    loadDashboard();
                }).catch(function(err) {
                    if (err.data && err.data.errors) {
                        var msgs = [];
                        for (var key in err.data.errors) {
                            msgs.push(err.data.errors[key][0]);
                        }
                        $scope.errorMsg = msgs.join(', ');
                    } else {
                        $scope.errorMsg = 'Erro ao salvar';
                    }
                });
            };
            
            $scope.updateStatus = function(os) {
                os.status = 'pago';
                $http.put(API + '/ordem-servicos/' + os.id, os).then(function() {
                    loadOS();
                    loadDashboard();
                });
            };
            
            $scope.deleteOS = function(os) {
                if(confirm('Excluir esta OS?')) {
                    $http.delete(API + '/ordem-servicos/' + os.id).then(function() {
                        loadOS();
                        loadDashboard();
                    });
                }
            };
        });
    </script>
</body>
</html>
