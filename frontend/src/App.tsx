import { BrowserRouter, Routes, Route, Link, useNavigate } from 'react-router-dom';
import { Button, AppBar, Toolbar, Typography, Box } from '@mui/material'; // <-- ADICIONADO 'Box'
import AuthCallback from './pages/AuthCallback';
import LoginPage from './pages/LoginPage';
import OrdersPage from './pages/OrdersPage';
import DashboardPage from './pages/DashboardPage';
import ProtectedRoute from './components/ProtectedRoute';
import { useAuthStore } from './stores/authStore';

function App() {
  const { isAuthenticated, logout } = useAuthStore();
  const navigate = useNavigate();

  const handleLogout = () => {
    logout();
    navigate('/login');
  };

  return (
    <>
      <AppBar position="static">
        <Toolbar>
          <Typography variant="h6" component="div" sx={{ flexGrow: 1 }}>
            {/* O link principal agora aponta para o dashboard */}
            <Link to={isAuthenticated ? "/dashboard" : "/login"} style={{ textDecoration: 'none', color: 'inherit' }}>
              LogManager
            </Link>
          </Typography>
          {/* Só mostra os botões de navegação se o utilizador estiver autenticado */}
          {isAuthenticated && (
            <Box>
              <Button color="inherit" component={Link} to="/dashboard">
                Dashboard
              </Button>
              <Button color="inherit" component={Link} to="/pedidos">
                Pedidos
              </Button>
              <Button color="inherit" onClick={handleLogout}>
                Logout
              </Button>
            </Box>
          )}
        </Toolbar>
      </AppBar>
      <Routes>
        {/* Rotas Públicas */}
        <Route path="/login" element={<LoginPage />} />
        <Route path="/auth/success" element={<AuthCallback />} />
        
        {/* Rotas Protegidas */}
        <Route element={<ProtectedRoute />}>
          <Route path="/dashboard" element={<DashboardPage />} /> {/* <-- ROTA DESCOMENTADA */}
          <Route path="/pedidos" element={<OrdersPage />} />
        </Route>

        {/* Rota principal: leva para o dashboard ou para o login */}
        <Route path="/" element={isAuthenticated ? <DashboardPage /> : <LoginPage />} />
      </Routes>
    </>
  );
}

function Root() {
  return (
    <BrowserRouter>
      <App />
    </BrowserRouter>
  );
}

export default Root;