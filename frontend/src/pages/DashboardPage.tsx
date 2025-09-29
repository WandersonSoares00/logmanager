import { useState, useEffect } from 'react';
import apiClient from '../api/axios';

import {
  Container,
  Typography,
  Grid,
  Card,
  CardContent,
  Box,
  CircularProgress,
  Alert,
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
  Paper
} from '@mui/material';

interface SlaData {
  average_sla_in_hours: number;
  total_orders_in_period: number;
  start_of_week: string;
  end_of_week: string;
}

interface Order {
  id: number;
  status: string;
  shipped_at: string;
  meliAccount: {
    nickname: string;
  };
}

function DashboardPage() {
  const [slaData, setSlaData] = useState<SlaData | null>(null);
  const [shippedToday, setShippedToday] = useState<Order[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const fetchDashboardData = async () => {
      try {
        setLoading(true);
        setError(null);

        // Usamos Promise.all para fazer as duas chamadas à API em paralelo
        const [slaResponse, shippedTodayResponse] = await Promise.all([
          apiClient.get('/reports/sla-weekly'),
          apiClient.get('/reports/shipped-today')
        ]);

        setSlaData(slaResponse.data);
        setShippedToday(shippedTodayResponse.data);

      } catch (err) {
        setError('Falha ao buscar os dados do dashboard.');
        console.error(err);
      } finally {
        setLoading(false);
      }
    };

    fetchDashboardData();
  }, []);

  if (loading) {
    return <Box display="flex" justifyContent="center" py={5}><CircularProgress /></Box>;
  }

  if (error) {
    return <Container sx={{mt: 4}}><Alert severity="error">{error}</Alert></Container>;
  }

  return (
    <Container maxWidth="lg" sx={{ mt: 4, mb: 4 }}>
      <Typography variant="h4" gutterBottom>
        Dashboard de Performance
      </Typography>

      <Grid container spacing={3}>
        {/* Card do SLA Semanal */}
        <Grid item xs={12}>
          <Card>
            <CardContent>
              <Typography color="text.secondary" gutterBottom>
                SLA Médio de Envio (Esta Semana)
              </Typography>
              <Typography variant="h3" component="div">
                {slaData ? slaData.average_sla_in_hours.toFixed(2) : '0.00'} horas
              </Typography>
              <Typography color="text.secondary">
                {`Baseado em ${slaData ? slaData.total_orders_in_period : '0'} pedidos enviados entre ${slaData ? new Date(slaData.start_of_week).toLocaleDateString('pt-BR') : ''} e ${slaData ? new Date(slaData.end_of_week).toLocaleDateString('pt-BR') : ''}`}
              </Typography>
            </CardContent>
          </Card>
        </Grid>

        {/* Tabela de Pedidos Enviados Hoje */}
        <Grid item xs={12}>
          <Typography variant="h5" gutterBottom sx={{ mt: 2 }}>
            Pedidos Enviados Hoje ({new Date().toLocaleDateString('pt-BR')})
          </Typography>
          <TableContainer component={Paper}>
            <Table>
              <TableHead>
                <TableRow>
                  <TableCell>ID do Pedido</TableCell>
                  <TableCell>Cliente (Nickname)</TableCell>
                  <TableCell>Status</TableCell>
                  <TableCell>Data de Envio</TableCell>
                </TableRow>
              </TableHead>
              <TableBody>
                {shippedToday.length === 0 ? (
                  <TableRow>
                    <TableCell colSpan={4} align="center">Nenhum pedido enviado hoje.</TableCell>
                  </TableRow>
                ) : (
                  shippedToday.map((order) => (
                    <TableRow key={order.id}>
                      <TableCell>{order.id}</TableCell>
                      <TableCell>{order.meliAccount?.nickname || 'N/A'}</TableCell>
                      <TableCell>{order.status}</TableCell>
                      <TableCell>{new Date(order.shipped_at).toLocaleString('pt-BR')}</TableCell>
                    </TableRow>
                  ))
                )}
              </TableBody>
            </Table>
          </TableContainer>
        </Grid>
      </Grid>
    </Container>
  );
}

export default DashboardPage;