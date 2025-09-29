import { useEffect } from 'react';
import { useSearchParams, useNavigate } from 'react-router-dom';
import { useAuthStore } from '../stores/authStore.ts';

function AuthCallback() {
  const [searchParams] = useSearchParams();
  const navigate = useNavigate();
  const login = useAuthStore((state) => state.login);

  useEffect(() => {
    const token = searchParams.get('token');
    if (token) {
      login(token);
      navigate('/pedidos');
    } else {
      navigate('/login');
    }
  }, [searchParams, login, navigate]);

  return <div>A processar autenticação...</div>;
}

export default AuthCallback;