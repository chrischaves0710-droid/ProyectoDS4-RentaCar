
import http from 'k6/http';
import { check } from 'k6';

export const options = {
  scenarios: {
    listado_vehiculos: {
      executor: 'constant-arrival-rate',
      rate: 10,
      timeUnit: '1s',
      duration: '30s',
      preAllocatedVUs: 5,
      maxVUs: 20,
    },
  },

  thresholds: {
    http_req_failed: ['rate<0.01'],
    http_req_duration: ['p(95)<1000'],
  },
};

const BASE_URL = __ENV.BASE_URL || 'http://127.0.0.1:3000';
const API_PATH = __ENV.API_PATH || '/api/vehiculos?page=1&per_page=10';

export default function () {
  const headers = {
    Accept: 'application/json',
  };

  if (__ENV.API_TOKEN) {
    headers.Authorization = `Bearer ${__ENV.API_TOKEN}`;
  }

  const respuesta = http.get(`${BASE_URL}${API_PATH}`, {
    headers,
  });

  check(respuesta, {
    'HTTP 200': (r) => r.status === 200,
  });
}
