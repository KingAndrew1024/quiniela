const USE_DEVELOP = location.host.includes('localhost')


export const API_HOST = USE_DEVELOP
  ? 'http://localhost:8123/Quiniela2026/api/index.php'
  : 'https://vbandrew.mx/quiniela2026/api/index.php'
