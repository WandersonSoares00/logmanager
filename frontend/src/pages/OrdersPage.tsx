import { useState, useEffect } from 'react';
import apiClient from '../api/axios'; // O seu cliente de API configurado
import Grid from '@mui/material/Grid';

import {
  Container, Typography, Table, TableBody, TableCell, TableContainer,
  TableHead, TableRow, Paper, Button, CircularProgress, Alert, Box,
  TextField, Select, MenuItem, FormControl, InputLabel
} from '@mui/material';

interface Order {
  id: number;
  status: string;
  total_amount: string;
  paid_at: string | null;
  label_download_url: string | null;
}

interface MeliAccount {
  nickname: string;
}

function OrdersPage() {
  const [orders, setOrders] = useState<Order[]>([]);
  const [accounts, setAccounts] = useState<MeliAccount[]>([]); // Para popular o dropdown de clientes
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  const [filters, setFilters] = useState({
    accountId: '',
    startDate: '',
    endDate: '',
  });

  const fetchOrders = async (currentFilters: typeof filters) => {
    try {
      setLoading(true);
      setError(null);

      const params = new URLSearchParams();
      if (currentFilters.accountId) {
        params.append('meli_account_id', currentFilters.accountId);
      }
      if (currentFilters.startDate) {
        params.append('start_date', currentFilters.startDate);
      }
      if (currentFilters.endDate) {
        params.append('end_date', currentFilters.endDate);
      }
      
      const response = await apiClient.get('/orders', { params });
      setOrders(response.data.data);
    } catch (err) {
      setError('Falha ao buscar os pedidos. Verifique a sua conexão ou tente autenticar novamente.');
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

    const handleDownloadLabel = async (orderId: number) => {
    try {
      const response = await apiClient.get(`/orders/${orderId}/label`, {
        responseType: 'blob',
      });

      const url = window.URL.createObjectURL(new Blob([response.data]));
      
      const link = document.createElement('a');
      link.href = url;
      
      link.setAttribute('download', `etiqueta-pedido-${orderId}.pdf`);
      
      document.body.appendChild(link);
      link.click();
      link.remove();
      window.URL.revokeObjectURL(url);

    } catch (error) {
      console.error('Erro ao baixar a etiqueta:', error);
      alert('Não foi possível baixar a etiqueta.');
    }
  };

  useEffect(() => {
    const fetchInitialData = async () => {
      try {
        const accountsResponse = await apiClient.get('/meli-accounts');
        setAccounts(accountsResponse.data);
      } catch (err) {
        console.error("Falha ao buscar contas do Meli", err);
        setError("Não foi possível carregar a lista de clientes para o filtro.");
      }
      await fetchOrders(filters);
    };
    
    fetchInitialData();
  }, []);

  const handleFilterChange = (e: React.ChangeEvent<HTMLInputElement | { name?: string; value: unknown }>) => {
    const { name, value } = e.target;
    setFilters(prevFilters => ({
      ...prevFilters,
      [name as string]: value,
    }));
  };

  const handleApplyFilters = () => {
    fetchOrders(filters);
  };

  const handleClearFilters = () => {
    const clearedFilters = { accountId: '', startDate: '', endDate: '' };
    setFilters(clearedFilters);
    fetchOrders(clearedFilters);
  };

  return (
    <Container maxWidth="lg">
      <Typography variant="h4" gutterBottom sx={{ mt: 4, mb: 2 }}>
        Meus Pedidos
      </Typography>

      {/* Painel de filtros */}
      <Paper elevation={2} sx={{ p: 2, mb: 3 }}>
        <Grid container spacing={2} alignItems="flex-end">
          <Grid item xs={12} md={4}>
            <FormControl fullWidth>
              <InputLabel id="account-select-label">Filtrar por Cliente</InputLabel>
              <Select
                labelId="account-select-label"
                name="accountId"
                value={filters.accountId}
                label="Filtrar por Cliente"
                onChange={handleFilterChange as any}
              >
                <MenuItem value="">
                  <em>Todos os Clientes</em>
                </MenuItem>
                {accounts.map(account => (
                  <MenuItem key={account.id} value={account.id}>
                    {account.nickname}
                  </MenuItem>
                ))}
              </Select>
            </FormControl>
          </Grid>
          <Grid item xs={6} md={3}>
            <TextField
              name="startDate"
              label="Data de Início"
              type="date"
              fullWidth
              value={filters.startDate}
              onChange={handleFilterChange}
              InputLabelProps={{ shrink: true }}
            />
          </Grid>
          <Grid xs={6} md={3}>
            <TextField
              name="endDate"
              label="Data de Fim"
              type="date"
              fullWidth
              value={filters.endDate}
              onChange={handleFilterChange}
              InputLabelProps={{ shrink: true }}
            />
          </Grid>
          <Grid xs={12} md={2}>
            <Box display="flex" gap={1}>
              <Button variant="contained" onClick={handleApplyFilters} fullWidth>Filtrar</Button>
              <Button variant="outlined" onClick={handleClearFilters} fullWidth>Limpar</Button>
            </Box>
          </Grid>
        </Grid>
      </Paper>

      {/* Tabela de pedidos */}
      {loading ? (
        <Box display="flex" justifyContent="center" py={5}><CircularProgress /></Box>
      ) : error ? (
        <Alert severity="error">{error}</Alert>
      ) : (
        <TableContainer component={Paper}>
          <Table>
            <TableHead>
              <TableRow>
                <TableCell>ID</TableCell>
                <TableCell>Status</TableCell>
                <TableCell>Total</TableCell>
                <TableCell>Data de Pagamento</TableCell>
                <TableCell>Etiqueta</TableCell>
              </TableRow>
            </TableHead>
            <TableBody>
              {orders.length === 0 ? (
                <TableRow>
                  <TableCell colSpan={5} align="center">Nenhum pedido encontrado para os filtros selecionados.</TableCell>
                </TableRow>
              ) : (
                orders.map((order) => (
                  <TableRow key={order.id}>
                    <TableCell>{order.id}</TableCell>
                    <TableCell>{order.status}</TableCell>
                    <TableCell>R$ {order.total_amount}</TableCell>
                    <TableCell>{order.paid_at ? new Date(order.paid_at).toLocaleDateString('pt-BR') : 'N/A'}</TableCell>
                    <TableCell>
                      {order.label_download_url ? (
                        <Button
                          variant="contained"
                          onClick={() => handleDownloadLabel(order.id)}
                        >
                          Baixar
                        </Button>
                      ) : ( 'Indisponível' )}
                    </TableCell>
                  </TableRow>
                ))
              )}
            </TableBody>
          </Table>
        </TableContainer>
      )}
    </Container>
  );
}

export default OrdersPage;