// frontend/src/pages/LoginPage.jsx
import { Button, Container, Typography, Box } from '@mui/material';

function LoginPage() {
  const meliAuthUrl = 'https://pitchier-murmurously-roberta.ngrok-free.dev/auth/meli/redirect';

  return (
    <Container>
      <Box
        display="flex"
        flexDirection="column"
        justifyContent="center"
        alignItems="center"
        minHeight="80vh"
      >
        <Typography variant="h4" gutterBottom>
          LogManager
        </Typography>
        <Typography variant="subtitle1" sx={{ mb: 4 }}>
          Para continuar, conecte-se com sua conta do Mercado Livre.
        </Typography>
        <Button
          variant="contained"
          color="primary"
          href={meliAuthUrl}
          sx={{
            backgroundColor: '#FFF159',
            color: '#000',
            '&:hover': { backgroundColor: '#E0D140' },
          }}
        >
          Conectar com Mercado Livre
        </Button>
      </Box>
    </Container>
  );
}

export default LoginPage;