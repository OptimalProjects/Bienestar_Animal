/**
 * Test para la integración del servicio de inventario
 * Ubicación: frontend/src/services/__tests__/inventoryService.test.js
 */

import { describe, it, expect, vi, beforeEach } from 'vitest';
import inventoryService from '../inventoryService';
import api from '../api';

// Mock del módulo api
vi.mock('../api');

describe('InventoryService', () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  describe('getInventario', () => {
    it('debe obtener lista de inventario con parámetros', async () => {
      const mockData = {
        data: [
          {
            id: '1',
            codigo: 'MED-001',
            nombre: 'Amoxicilina',
            stock_actual: 50
          }
        ]
      };

      api.get.mockResolvedValue(mockData);

      const result = await inventoryService.getInventario({ per_page: 15 });

      expect(api.get).toHaveBeenCalledWith('/inventario', { params: { per_page: 15 } });
      expect(result).toEqual(mockData.data);
    });
  });

  describe('crearInventario', () => {
    it('debe crear un nuevo item de inventario', async () => {
      const mockData = {
        data: {
          id: '1',
          codigo: 'MED-001',
          nombre: 'Amoxicilina',
          stock_actual: 50
        }
      };

      const requestData = {
        codigo: 'MED-001',
        nombre: 'Amoxicilina',
        cantidad_actual: 50
      };

      api.post.mockResolvedValue(mockData);

      const result = await inventoryService.crearInventario(requestData);

      expect(api.post).toHaveBeenCalledWith('/inventario', requestData);
      expect(result).toEqual(mockData.data);
    });
  });

  describe('registrarEntrada', () => {
    it('debe registrar una entrada de stock', async () => {
      const mockData = {
        data: { success: true }
      };

      const entryData = {
        tipo: 'insumo',
        cantidad: 25,
        motivo: 'Reposición'
      };

      api.post.mockResolvedValue(mockData);

      const result = await inventoryService.registrarEntrada('item-id', entryData);

      expect(api.post).toHaveBeenCalledWith('/inventario/item-id/entrada', entryData);
      expect(result).toEqual(mockData.data);
    });
  });

  describe('registrarSalida', () => {
    it('debe registrar una salida de stock', async () => {
      const mockData = {
        data: { success: true }
      };

      const salidaData = {
        tipo: 'insumo',
        cantidad: 10,
        motivo: 'Uso en consulta'
      };

      api.post.mockResolvedValue(mockData);

      const result = await inventoryService.registrarSalida('item-id', salidaData);

      expect(api.post).toHaveBeenCalledWith('/inventario/item-id/salida', salidaData);
      expect(result).toEqual(mockData.data);
    });
  });

  describe('getStockBajo', () => {
    it('debe obtener items con stock bajo', async () => {
      const mockData = {
        data: {
          inventario: [],
          insumos: [
            {
              id: '1',
              nombre: 'Medicamento A',
              stock_actual: 5,
              stock_minimo: 10
            }
          ],
          medicamentos: []
        }
      };

      api.get.mockResolvedValue(mockData);

      const result = await inventoryService.getStockBajo();

      expect(api.get).toHaveBeenCalledWith('/inventario/stock-bajo');
      expect(result).toEqual(mockData.data);
    });
  });

  describe('getProximosVencer', () => {
    it('debe obtener items próximos a vencer', async () => {
      const mockData = {
        data: {
          insumos: [
            {
              id: '1',
              nombre: 'Medicamento A',
              fecha_vencimiento: '2025-02-21'
            }
          ]
        }
      };

      api.get.mockResolvedValue(mockData);

      const result = await inventoryService.getProximosVencer(30);

      expect(api.get).toHaveBeenCalledWith('/inventario/proximos-vencer', {
        params: { dias: 30 }
      });
      expect(result).toEqual(mockData.data);
    });
  });

  describe('verificarStock', () => {
    it('debe verificar disponibilidad de stock', async () => {
      const mockData = {
        data: { disponible: true }
      };

      api.get.mockResolvedValue(mockData);

      const result = await inventoryService.verificarStock({
        tipo: 'insumo',
        id: 'item-id',
        cantidad: 20
      });

      expect(api.get).toHaveBeenCalledWith('/inventario/verificar-stock', {
        params: { tipo: 'insumo', id: 'item-id', cantidad: 20 }
      });
      expect(result).toEqual(mockData.data);
    });
  });

  describe('getEstadisticas', () => {
    it('debe obtener estadísticas de inventario', async () => {
      const mockData = {
        data: {
          inventario: {
            total_items: 100,
            items_stock_bajo: 5,
            items_sin_stock: 2
          }
        }
      };

      api.get.mockResolvedValue(mockData);

      const result = await inventoryService.getEstadisticas();

      expect(api.get).toHaveBeenCalledWith('/inventario/estadisticas');
      expect(result).toEqual(mockData.data);
    });
  });
});
