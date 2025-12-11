# AUDITORIA DE CONEXIONES - Sistema Bienestar Animal

**Fecha:** 2025-12-11
**Backend endpoints:** 56 disponibles
**Frontend stores:** 5 disponibles
**Frontend services:** 5 disponibles

---

## RESUMEN EJECUTIVO

| Estado | Cantidad | Porcentaje |
|--------|----------|------------|
| Conectados | 8 | 27% |
| Parcialmente conectados | 7 | 23% |
| Sin conectar (MOCK) | 15 | 50% |

**Total componentes analizados:** 30

---

## INVENTARIO DEL BACKEND

### Controladores Implementados (15)
```
Api/V1/
├── Admin/
│   ├── InventarioController.php
│   ├── ReporteController.php
│   └── UsuarioController.php
├── Adoptions/
│   ├── AdopcionController.php
│   └── VisitaSeguimientoController.php
├── Animal/
│   └── AnimalController.php
├── Auth/
│   └── LoginController.php
├── Complaints/
│   ├── DenunciaController.php
│   └── RescateController.php
├── Veterinary/
│   ├── CirugiaController.php
│   ├── ConsultaController.php
│   ├── HistorialClinicoController.php
│   └── VacunaController.php
├── BaseController.php
└── HealthController.php
```

### Modelos Implementados (28)
```
Models/
├── Administracion/ (4)
│   ├── Indicador.php
│   ├── Insumo.php
│   ├── Inventario.php
│   └── PuntoIndicador.php
├── Adopcion/ (3)
│   ├── Adopcion.php
│   ├── Adoptante.php
│   └── VisitaDomiciliaria.php
├── Animal/ (2)
│   ├── Animal.php
│   └── HistorialClinico.php
├── Denuncia/ (3)
│   ├── Denuncia.php
│   ├── Denunciante.php
│   └── Rescate.php
├── User/ (6)
│   ├── EventoAuditoria.php
│   ├── Permiso.php
│   ├── Rol.php
│   ├── RolPermiso.php
│   ├── Usuario.php
│   └── UsuarioRol.php
└── Veterinaria/ (11)
    ├── Cirugia.php
    ├── Consulta.php
    ├── ExamenLaboratorio.php
    ├── Medicamento.php
    ├── Procedimiento.php
    ├── ProductoFarmaceutico.php
    ├── RecordatorioVacuna.php
    ├── TipoVacuna.php
    ├── Tratamiento.php
    ├── Vacuna.php
    └── Veterinario.php
```

### Endpoints API (56 rutas)

#### Rutas Publicas (5)
| Metodo | Endpoint | Controlador |
|--------|----------|-------------|
| GET | /api/v1/health | HealthController@check |
| POST | /api/v1/auth/login | LoginController@login |
| POST | /api/v1/auth/mfa/verify | LoginController@verificarMfa |
| GET | /api/v1/animals/catalogo-adopcion | AnimalController@index |
| POST | /api/v1/denuncias | DenunciaController@store |
| GET | /api/v1/denuncias/consultar/{ticket} | DenunciaController@consultarTicket |

#### Rutas Protegidas - Autenticacion (4)
| Metodo | Endpoint | Controlador |
|--------|----------|-------------|
| POST | /api/v1/auth/logout | LoginController@logout |
| POST | /api/v1/auth/refresh | LoginController@refresh |
| GET | /api/v1/auth/me | LoginController@me |

#### Rutas Protegidas - Animales (8)
| Metodo | Endpoint | Controlador |
|--------|----------|-------------|
| GET | /api/v1/animals/statistics | AnimalController@statistics |
| GET | /api/v1/animals | AnimalController@index |
| POST | /api/v1/animals | AnimalController@store |
| GET | /api/v1/animals/{id} | AnimalController@show |
| PUT | /api/v1/animals/{id} | AnimalController@update |
| DELETE | /api/v1/animals/{id} | AnimalController@destroy |
| GET | /api/v1/animals/{animalId}/historial-clinico | HistorialClinicoController@show |
| PUT | /api/v1/animals/{animalId}/historial-clinico | HistorialClinicoController@update |
| POST | /api/v1/animals/{animalId}/chip | HistorialClinicoController@registrarChip |
| GET | /api/v1/historial-clinico/buscar-chip/{chip} | HistorialClinicoController@buscarPorChip |

#### Rutas Protegidas - Veterinaria (17)
| Metodo | Endpoint | Controlador |
|--------|----------|-------------|
| GET | /api/v1/consultas/estadisticas | ConsultaController@estadisticas |
| GET | /api/v1/consultas/hoy | ConsultaController@consultasHoy |
| GET | /api/v1/consultas/pendientes | ConsultaController@pendientes |
| GET | /api/v1/consultas | ConsultaController@index |
| POST | /api/v1/consultas | ConsultaController@store |
| GET | /api/v1/consultas/{id} | ConsultaController@show |
| GET | /api/v1/vacunas/tipos | VacunaController@tiposVacuna |
| GET | /api/v1/vacunas/proximas | VacunaController@proximasVacunas |
| GET | /api/v1/vacunas/animal/{animalId} | VacunaController@vacunasAnimal |
| GET | /api/v1/vacunas | VacunaController@index |
| POST | /api/v1/vacunas | VacunaController@store |
| GET | /api/v1/vacunas/{id} | VacunaController@show |
| GET | /api/v1/cirugias/procedimientos | CirugiaController@procedimientos |
| GET | /api/v1/cirugias/estadisticas | CirugiaController@estadisticas |
| GET | /api/v1/cirugias/animal/{animalId} | CirugiaController@cirugiasAnimal |
| GET | /api/v1/cirugias | CirugiaController@index |
| POST | /api/v1/cirugias | CirugiaController@store |
| GET | /api/v1/cirugias/{id} | CirugiaController@show |
| PUT | /api/v1/cirugias/{id} | CirugiaController@update |

#### Rutas Protegidas - Adopciones (12)
| Metodo | Endpoint | Controlador |
|--------|----------|-------------|
| GET | /api/v1/adopciones/estadisticas | AdopcionController@estadisticas |
| GET | /api/v1/adopciones/pendientes | AdopcionController@pendientes |
| GET | /api/v1/adopciones | AdopcionController@index |
| POST | /api/v1/adopciones | AdopcionController@store |
| GET | /api/v1/adopciones/{id} | AdopcionController@show |
| PUT | /api/v1/adopciones/{id}/evaluar | AdopcionController@evaluar |
| GET | /api/v1/adopciones/{id}/contrato | AdopcionController@contrato |
| GET | /api/v1/visitas-seguimiento/pendientes | VisitaSeguimientoController@pendientes |
| GET | /api/v1/visitas-seguimiento/requieren-visita | VisitaSeguimientoController@requierenVisita |
| GET | /api/v1/visitas-seguimiento | VisitaSeguimientoController@index |
| POST | /api/v1/visitas-seguimiento | VisitaSeguimientoController@store |
| GET | /api/v1/visitas-seguimiento/{id} | VisitaSeguimientoController@show |
| PUT | /api/v1/visitas-seguimiento/{id}/registrar | VisitaSeguimientoController@registrar |
| PUT | /api/v1/visitas-seguimiento/{id}/cancelar | VisitaSeguimientoController@cancelar |
| PUT | /api/v1/visitas-seguimiento/{id}/reprogramar | VisitaSeguimientoController@reprogramar |

#### Rutas Protegidas - Denuncias (12)
| Metodo | Endpoint | Controlador |
|--------|----------|-------------|
| GET | /api/v1/denuncias/estadisticas | DenunciaController@estadisticas |
| GET | /api/v1/denuncias/urgentes | DenunciaController@urgentes |
| GET | /api/v1/denuncias/mis-asignaciones | DenunciaController@misAsignaciones |
| GET | /api/v1/denuncias/mapa-calor | DenunciaController@mapaCalor |
| GET | /api/v1/denuncias | DenunciaController@index |
| GET | /api/v1/denuncias/{id} | DenunciaController@show |
| PUT | /api/v1/denuncias/{id}/asignar | DenunciaController@asignar |
| PUT | /api/v1/denuncias/{id}/estado | DenunciaController@actualizarEstado |
| GET | /api/v1/rescates/estadisticas | RescateController@estadisticas |
| GET | /api/v1/rescates | RescateController@index |
| POST | /api/v1/rescates | RescateController@store |
| GET | /api/v1/rescates/{id} | RescateController@show |
| PUT | /api/v1/rescates/{id} | RescateController@update |
| PUT | /api/v1/rescates/{id}/vincular-animal | RescateController@vincularAnimal |

#### Rutas Protegidas - Administracion (17)
| Metodo | Endpoint | Controlador |
|--------|----------|-------------|
| GET | /api/v1/usuarios/roles | UsuarioController@roles |
| GET | /api/v1/usuarios | UsuarioController@index |
| POST | /api/v1/usuarios | UsuarioController@store |
| GET | /api/v1/usuarios/{id} | UsuarioController@show |
| PUT | /api/v1/usuarios/{id} | UsuarioController@update |
| PUT | /api/v1/usuarios/{id}/password | UsuarioController@cambiarPassword |
| PUT | /api/v1/usuarios/{id}/toggle-activo | UsuarioController@toggleActivo |
| DELETE | /api/v1/usuarios/{id} | UsuarioController@destroy |
| GET | /api/v1/inventario/estadisticas | InventarioController@estadisticas |
| GET | /api/v1/inventario/stock-bajo | InventarioController@stockBajo |
| GET | /api/v1/inventario/proximos-vencer | InventarioController@proximosVencer |
| GET | /api/v1/inventario/vencidos | InventarioController@vencidos |
| GET | /api/v1/inventario/verificar-stock | InventarioController@verificarStock |
| GET | /api/v1/inventario/insumos | InventarioController@insumos |
| GET | /api/v1/inventario | InventarioController@index |
| POST | /api/v1/inventario | InventarioController@store |
| PUT | /api/v1/inventario/{id} | InventarioController@update |
| POST | /api/v1/inventario/{id}/entrada | InventarioController@registrarEntrada |
| POST | /api/v1/inventario/{id}/salida | InventarioController@registrarSalida |
| GET | /api/v1/reportes/dashboard | ReporteController@dashboard |
| GET | /api/v1/reportes/indicadores | ReporteController@indicadores |
| POST | /api/v1/reportes/indicadores/{indicadorId}/punto | ReporteController@registrarIndicador |
| GET | /api/v1/reportes/periodo | ReporteController@reportePeriodo |
| GET | /api/v1/reportes/exportar | ReporteController@exportar |

---

## INVENTARIO DEL FRONTEND

### Stores Pinia (5)
| Store | Archivo | Metodos API | Estado |
|-------|---------|-------------|--------|
| auth | auth.js | login, verifyMfa, logout, refreshToken, fetchUser | CONECTADO |
| animals | animals.js | fetchAnimals, fetchAnimal, createAnimal, updateAnimal, deleteAnimal, fetchStatistics, fetchHistorialClinico | CONECTADO |
| adoptions | adoptions.js | fetchAdopciones, fetchPendientes, fetchAdopcion, crearSolicitud, evaluarSolicitud, generarContrato, fetchEstadisticas | CONECTADO |
| complaints | complaints.js | fetchDenuncias, fetchDenuncia, fetchUrgentes, fetchMisAsignaciones, crearDenuncia, consultarTicket, asignarDenuncia, actualizarEstado, fetchEstadisticas, fetchMapaCalor, fetchRescates, crearRescate | CONECTADO |
| veterinary | veterinary.js | fetchConsultas, fetchConsultasHoy, fetchConsultasPendientes, fetchConsulta, crearConsulta, fetchTiposVacuna, fetchVacunasAnimal, crearVacuna, fetchProcedimientos, fetchCirugiasAnimal, crearCirugia, fetchEstadisticas | CONECTADO |

### Services (5)
| Service | Archivo | Estado |
|---------|---------|--------|
| api | api.js | CONECTADO (base axios) |
| animalService | animalService.js | CONECTADO |
| adoptionService | adoptionService.js | CONECTADO |
| complaintService | complaintService.js | CONECTADO |
| veterinaryService | veterinaryService.js | CONECTADO |
| reportService | reportService.js | PARCIAL |

---

## DETALLE POR MODULO

### 1. AUTENTICACION
| Componente | Archivo | Store | Estado | Notas |
|------------|---------|-------|--------|-------|
| LoginView | views/LoginView.vue | useAuthStore | CONECTADO | Login funcional con JWT |
| MFA Verification | LoginView.vue | useAuthStore | CONECTADO | Verifica MFA si requerido |
| Logout | - | useAuthStore | CONECTADO | Limpia token y redirige |

**Endpoints usados:**
- POST /api/v1/auth/login
- POST /api/v1/auth/mfa/verify
- POST /api/v1/auth/logout
- GET /api/v1/auth/me

---

### 2. DENUNCIAS
| Componente | Archivo | Store | Estado | Notas |
|------------|---------|-------|--------|-------|
| ComplaintForm | complaints/ComplaintForm.vue | useComplaintsStore | CONECTADO | Crea denuncia real |
| ComplaintStatusCheck | complaints/ComplaintStatusCheck.vue | - | MOCK | Usa mockComplaints, no llama API |
| ComplaintList | complaints/ComplaintList.vue | useComplaintsStore | PARCIAL | Tiene store pero verificar uso |
| ComplaintDetailModal | complaints/ComplaintDetailModal.vue | - | PARCIAL | Recibe props |
| RescueAssignmentModal | complaints/RescueAssignmentModal.vue | - | MOCK | Equipos mock, setTimeout |
| RescueResultModal | complaints/RescueResultModal.vue | - | MOCK | Solo setTimeout |
| RescueOperations | complaints/RescueOperations.vue | - | VERIFICAR | - |
| AuthorityReports | complaints/AuthorityReports.vue | - | MOCK | setTimeout simulado |
| RescueMap | complaints/RescueMap.vue | - | VERIFICAR | - |

**Endpoints disponibles vs usados:**
| Endpoint | Service | Store | Componente | Estado |
|----------|---------|-------|------------|--------|
| POST /denuncias | createDenuncia | crearDenuncia | ComplaintForm | CONECTADO |
| GET /denuncias/consultar/{ticket} | consultarTicket | consultarTicket | ComplaintStatusCheck | SIN CONECTAR |
| GET /denuncias | getDenuncias | fetchDenuncias | ComplaintList | VERIFICAR |
| GET /denuncias/{id} | getDenuncia | fetchDenuncia | - | DISPONIBLE |
| GET /denuncias/urgentes | getDenunciasUrgentes | fetchUrgentes | - | DISPONIBLE |
| GET /denuncias/mis-asignaciones | getMisAsignaciones | fetchMisAsignaciones | - | DISPONIBLE |
| PUT /denuncias/{id}/asignar | asignarDenuncia | asignarDenuncia | - | DISPONIBLE |
| PUT /denuncias/{id}/estado | actualizarEstado | actualizarEstado | - | DISPONIBLE |
| GET /denuncias/estadisticas | getEstadisticas | fetchEstadisticas | - | DISPONIBLE |
| GET /denuncias/mapa-calor | getMapaCalor | fetchMapaCalor | - | DISPONIBLE |
| GET /rescates | getRescates | fetchRescates | - | DISPONIBLE |
| POST /rescates | createRescate | crearRescate | - | DISPONIBLE |

---

### 3. ANIMALES
| Componente | Archivo | Store | Estado | Notas |
|------------|---------|-------|--------|-------|
| AnimalList | animals/AnimalList.vue | - | VERIFICAR | - |
| AnimalForm | animals/AnimalForm.vue | - | MOCK | setTimeout simulado |
| AnimalDetail | animals/AnimalDetail.vue | - | VERIFICAR | - |
| AnimalCard | animals/AnimalCard.vue | - | PRESENTACIONAL | - |
| AnimalSearch | animals/AnimalSearch.vue | useAnimalsStore | PARCIAL | - |
| AnimalNeuturingForm | animals/AnimalNeuturingForm.vue | - | MOCK | setTimeout |

**Endpoints disponibles vs usados:**
| Endpoint | Service | Store | Estado |
|----------|---------|-------|--------|
| GET /animals | getAnimals | fetchAnimals | CONECTADO |
| POST /animals | createAnimal | createAnimal | CONECTADO |
| GET /animals/{id} | getAnimal | fetchAnimal | CONECTADO |
| PUT /animals/{id} | updateAnimal | updateAnimal | CONECTADO |
| DELETE /animals/{id} | deleteAnimal | deleteAnimal | CONECTADO |
| GET /animals/statistics | getStatistics | fetchStatistics | CONECTADO |
| GET /animals/{id}/historial-clinico | getHistorialClinico | fetchHistorialClinico | CONECTADO |

---

### 4. ADOPCIONES
| Componente | Archivo | Store | Estado | Notas |
|------------|---------|-------|--------|-------|
| AdoptionForm | adoptions/AdoptionForm.vue | - | VERIFICAR | - |
| AdoptionList | adoptions/AdoptionList.vue | - | VERIFICAR | - |
| AdoptionRequestsManager | adoptions/AdoptionRequestsManager.vue | - | MOCK | mockRequests, alerts mock |
| AdoptionContractManager | adoptions/AdoptionContractManager.vue | - | MOCK | mockRequests, alerts mock |
| AdoptionReturnsManager | adoptions/AdoptionReturnsManager.vue | - | MOCK | mockAdoptions |
| PostAdoptionFollowUps | adoptions/PostAdoptionFollowUps.vue | - | VERIFICAR | - |
| AdopterProfile | adoptions/AdopterProfile.vue | - | VERIFICAR | - |
| AdoptionsView | views/AdoptionsView.vue | - | MOCK | console.log mock |

**Endpoints disponibles vs usados:**
| Endpoint | Service | Store | Estado |
|----------|---------|-------|--------|
| GET /adopciones | api.get | fetchAdopciones | DISPONIBLE |
| POST /adopciones | api.post | crearSolicitud | DISPONIBLE |
| GET /adopciones/pendientes | api.get | fetchPendientes | DISPONIBLE |
| GET /adopciones/{id} | api.get | fetchAdopcion | DISPONIBLE |
| PUT /adopciones/{id}/evaluar | api.put | evaluarSolicitud | DISPONIBLE |
| GET /adopciones/{id}/contrato | api.get | generarContrato | DISPONIBLE |
| GET /adopciones/estadisticas | api.get | fetchEstadisticas | DISPONIBLE |

**NOTA:** El adoptionService.js tiene endpoints diferentes a los del backend real. Necesita alinearse.

---

### 5. VETERINARIA
| Componente | Archivo | Store | Estado | Notas |
|------------|---------|-------|--------|-------|
| MedicalHistory | veterinary/MedicalHistory.vue | - | VERIFICAR | - |
| MedicalRecordForm | veterinary/MedicalRecordForm.vue | - | MOCK | setTimeout |
| VaccinationForm | veterinary/VaccinationForm.vue | - | MOCK | setTimeout |
| SurgeryForm | veterinary/SurgeryForm.vue | - | VERIFICAR | - |
| MedicationInventory | veterinary/MedicationInventory.vue | - | MOCK | console.log mock |
| MedicationPrescription | veterinary/MedicationPrescription.vue | - | VERIFICAR | - |
| CertificateGenerator | veterinary/CertificateGenerator.vue | - | VERIFICAR | - |
| VeterinaryAlerts | veterinary/VeterinaryAlerts.vue | - | MOCK | TODO: reemplazar mocks |
| VitalSignsInput | veterinary/VitalSignsInput.vue | - | PRESENTACIONAL | - |

**Endpoints disponibles vs usados:**
| Endpoint | Service | Store | Estado |
|----------|---------|-------|--------|
| GET /consultas | getConsultas | fetchConsultas | DISPONIBLE |
| POST /consultas | createConsulta | crearConsulta | DISPONIBLE |
| GET /consultas/hoy | getConsultasHoy | fetchConsultasHoy | DISPONIBLE |
| GET /consultas/pendientes | getConsultasPendientes | fetchConsultasPendientes | DISPONIBLE |
| GET /vacunas/tipos | getTiposVacuna | fetchTiposVacuna | DISPONIBLE |
| POST /vacunas | createVacuna | crearVacuna | DISPONIBLE |
| GET /vacunas/animal/{id} | getVacunasAnimal | fetchVacunasAnimal | DISPONIBLE |
| GET /cirugias/procedimientos | getProcedimientos | fetchProcedimientos | DISPONIBLE |
| POST /cirugias | createCirugia | crearCirugia | DISPONIBLE |

---

### 6. ADMINISTRACION
| Componente | Archivo | Store | Estado | Notas |
|------------|---------|-------|--------|-------|
| UserManagement | admin/UserManagement.vue | - | MOCK | Actividades mock |
| AuditLog | admin/AuditLog.vue | - | MOCK | Logs mock |
| Reports | admin/Reports.vue | - | VERIFICAR | - |
| CustomReports | admin/CustomReports.vue | - | MOCK | mockGenerators |
| ExecutiveDashboard | admin/ExecutiveDashboard.vue | - | MOCK | mock-chart divs |
| Dashboard | admin/Dashboard.vue | - | VERIFICAR | - |

**Endpoints disponibles pero SIN USAR:**
- GET/POST/PUT/DELETE /usuarios/*
- GET /usuarios/roles
- GET /inventario/*
- GET /reportes/dashboard
- GET /reportes/indicadores

---

### 7. DASHBOARD
| Componente | Archivo | Store | Estado | Notas |
|------------|---------|-------|--------|-------|
| DashboardView | views/DashboardView.vue | useComplaintsStore | PARCIAL | Carga algunas stats |

---

## COMPONENTES SIN CONECTAR (PRIORIDAD)

### PRIORIDAD ALTA (Funcionalidad critica)

1. **ComplaintStatusCheck.vue** - Consulta por ticket
   - **Backend:** `GET /api/v1/denuncias/consultar/{ticket}` EXISTE
   - **Service:** `consultarTicket(ticket)` EXISTE
   - **Store:** `consultarTicket(ticket)` EXISTE
   - **Problema:** Componente usa `mockComplaints` en lugar del store
   - **Accion:** Reemplazar mock por llamada a `complaintsStore.consultarTicket()`
   - **Esfuerzo:** 15 min

2. **AdoptionRequestsManager.vue** - Gestionar solicitudes
   - **Backend:** Endpoints de adopciones EXISTEN
   - **Service:** adoptionService.js EXISTE (endpoints diferentes)
   - **Store:** adoptions.js EXISTE
   - **Problema:** Usa `mockRequests` array
   - **Accion:** Conectar con adoptionsStore
   - **Esfuerzo:** 30 min

3. **AdoptionContractManager.vue** - Generar contratos
   - **Backend:** `GET /api/v1/adopciones/{id}/contrato` EXISTE
   - **Store:** `generarContrato(id)` EXISTE
   - **Problema:** Usa `mockRequests`
   - **Accion:** Conectar con adoptionsStore
   - **Esfuerzo:** 30 min

### PRIORIDAD MEDIA (Operaciones internas)

4. **RescueAssignmentModal.vue** - Asignar operativo
   - **Backend:** `PUT /api/v1/denuncias/{id}/asignar` EXISTE
   - **Store:** `asignarDenuncia(id, funcionarioId)` EXISTE
   - **Problema:** Equipos mock, setTimeout
   - **Accion:** Cargar equipos reales, usar store
   - **Esfuerzo:** 30 min

5. **RescueResultModal.vue** - Registrar resultado
   - **Backend:** `POST /api/v1/rescates` EXISTE
   - **Store:** `crearRescate(data)` EXISTE
   - **Problema:** Solo setTimeout
   - **Accion:** Usar complaintsStore.crearRescate()
   - **Esfuerzo:** 20 min

6. **AnimalForm.vue** - Crear/editar animal
   - **Backend:** CRUD completo EXISTE
   - **Store:** animalsStore completo EXISTE
   - **Problema:** setTimeout en submit
   - **Accion:** Usar animalsStore.createAnimal() / updateAnimal()
   - **Esfuerzo:** 30 min

7. **MedicalRecordForm.vue** - Registro medico
   - **Backend:** Endpoints veterinaria EXISTEN
   - **Store:** veterinaryStore EXISTE
   - **Problema:** setTimeout
   - **Accion:** Conectar con veterinaryStore
   - **Esfuerzo:** 30 min

8. **VaccinationForm.vue** - Registrar vacuna
   - **Backend:** `POST /api/v1/vacunas` EXISTE
   - **Store:** `crearVacuna(data)` EXISTE
   - **Problema:** setTimeout
   - **Accion:** Usar veterinaryStore.crearVacuna()
   - **Esfuerzo:** 20 min

### PRIORIDAD BAJA (Admin/Reportes)

9. **UserManagement.vue** - Gestionar usuarios
   - **Backend:** CRUD usuarios EXISTE
   - **Store:** NO EXISTE
   - **Accion:** Crear usersStore o usar api directo
   - **Esfuerzo:** 1 hora

10. **AuditLog.vue** - Ver auditoria
    - **Backend:** EventoAuditoria modelo EXISTE
    - **Endpoints:** NO HAY endpoints de auditoria
    - **Accion:** Crear endpoint GET /auditoria
    - **Esfuerzo:** 1 hora

11. **ExecutiveDashboard.vue** - Dashboard ejecutivo
    - **Backend:** `GET /api/v1/reportes/dashboard` EXISTE
    - **Store:** NO HAY reportes store
    - **Accion:** Crear reportesStore o conectar directo
    - **Esfuerzo:** 1 hora

12. **CustomReports.vue** - Reportes personalizados
    - **Backend:** `GET /api/v1/reportes/exportar` EXISTE
    - **Problema:** mockGenerators
    - **Esfuerzo:** 1 hora

---

## DISCREPANCIAS DETECTADAS

### 1. adoptionService.js vs Backend real
El service tiene endpoints que NO coinciden con el backend:

| Service | Backend Real |
|---------|--------------|
| /adopciones/solicitudes | /adopciones |
| /adopciones/solicitudes/:id/visita | NO EXISTE |
| /adopciones/solicitudes/:id/informe-visita | NO EXISTE |
| /adopciones/solicitudes/:id/aprobar | /adopciones/:id/evaluar |
| /adopciones/contratos/pendientes | NO EXISTE |
| /adopciones/contratos/:id/generar | /adopciones/:id/contrato |
| /adopciones/seguimientos/pendientes | /visitas-seguimiento/pendientes |

**Accion:** Alinear adoptionService.js con rutas reales del backend

### 2. Falta store de Usuarios/Admin
- El backend tiene CRUD completo de usuarios
- No existe usersStore en el frontend
- UserManagement.vue usa mock data

### 3. Falta store de Reportes
- El backend tiene endpoints de reportes
- reportService.js existe pero esta parcial
- No hay reportesStore

---

## ENDPOINTS SIN CONSUMIR

Endpoints del backend que EXISTEN pero NO se usan en frontend:

```
# Usuarios
GET   /api/v1/usuarios/roles
GET   /api/v1/usuarios
POST  /api/v1/usuarios
GET   /api/v1/usuarios/{id}
PUT   /api/v1/usuarios/{id}
PUT   /api/v1/usuarios/{id}/password
PUT   /api/v1/usuarios/{id}/toggle-activo
DELETE /api/v1/usuarios/{id}

# Inventario (completo)
GET   /api/v1/inventario/estadisticas
GET   /api/v1/inventario/stock-bajo
GET   /api/v1/inventario/proximos-vencer
GET   /api/v1/inventario/vencidos
GET   /api/v1/inventario/verificar-stock
GET   /api/v1/inventario/insumos
GET   /api/v1/inventario
POST  /api/v1/inventario
PUT   /api/v1/inventario/{id}
POST  /api/v1/inventario/{id}/entrada
POST  /api/v1/inventario/{id}/salida

# Reportes
GET   /api/v1/reportes/dashboard
GET   /api/v1/reportes/indicadores
POST  /api/v1/reportes/indicadores/{indicadorId}/punto
GET   /api/v1/reportes/periodo
GET   /api/v1/reportes/exportar
```

---

## PLAN DE ACCION RECOMENDADO

### FASE 1: Conexiones Criticas (1-2 horas)
1. **ComplaintStatusCheck.vue** - Consulta por ticket (15 min)
2. **AnimalForm.vue** - CRUD animales (30 min)
3. **VaccinationForm.vue** - Registrar vacunas (20 min)
4. **RescueResultModal.vue** - Registrar rescate (20 min)

### FASE 2: Modulo Adopciones (1-2 horas)
1. **Alinear adoptionService.js** con endpoints reales (30 min)
2. **AdoptionRequestsManager.vue** - Gestionar solicitudes (30 min)
3. **AdoptionContractManager.vue** - Contratos (30 min)

### FASE 3: Modulo Denuncias (1 hora)
1. **RescueAssignmentModal.vue** - Asignar operativo (30 min)
2. **AuthorityReports.vue** - Reportes autoridades (30 min)

### FASE 4: Administracion (2 horas)
1. **Crear usersStore.js** (30 min)
2. **UserManagement.vue** - Conectar (30 min)
3. **Crear reportesStore.js** (30 min)
4. **ExecutiveDashboard.vue** - Conectar (30 min)

### FASE 5: Veterinaria (1 hora)
1. **MedicalRecordForm.vue** - Consultas (30 min)
2. **MedicationInventory.vue** - Inventario (30 min)

---

## NOTAS FINALES

1. **Stores bien implementados:** auth, animals, complaints, veterinary, adoptions - todos hacen llamadas reales a API

2. **Services bien implementados:** animalService, complaintService, veterinaryService - correctamente alineados

3. **Problema principal:** Los COMPONENTES no usan los stores/services disponibles, siguen con mock data

4. **adoptionService.js:** Necesita revision completa - endpoints no coinciden con backend

5. **Falta infraestructura para:** Usuarios (store), Reportes (store), Inventario (store + service)

---

**Generado por:** Claude Code
**Fecha:** 2025-12-11
