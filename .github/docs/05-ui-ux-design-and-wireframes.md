# 05. UI/UX Design and Operational Wireframes

## 1. Information Architecture and Profile Navigation Maps

The user experience is split into three main flows, each tailored to a specific device, interaction model, and operational role at the venue.

### 1.1 Kiosk Touchscreen Flow (`kiosk`)
A full-screen layout designed for high transaction speed (target time per user: < 15 seconds).

```
+-------------------------------------------------------+
| 1. PANTALLA DE BIENVENIDA / SELECCIÓN TIPO ID         |
+---------------------------+---------------------------+
                            | (Selecciona DNI / Pasaporte / CE)
                            v
+-------------------------------------------------------+
| 2. TECLADO NUMÉRICO TÁCTIL E INGRESO ID               |
+---------------------------+---------------------------+
                            | (Consulta API externa)
            +---------------+---------------+
            |                               |
     (Éxito en API)                 (Falla / No encontrado)
            |                               |
            v                               v
+-----------------------+       +-----------------------+
| Autocompleta          |       | Solicita ingreso      |
| nombre (name)         |       | manual de nombre      |
+-----------+-----------+       +-----------+-----------+
            |                               |
            +---------------+---------------+
                            |
                            v
+-------------------------------------------------------+
| 3. SELECCIÓN DE CATEGORÍA Y CHECK PRIORIDAD           |
+---------------------------+---------------------------+
                            | (Presiona "Generar Ticket")
                            v
+-------------------------------------------------------+
| 4. CONFIRMACIÓN VISUAL E IMPRESIÓN TÉRMICA            |
+-------------------------------------------------------+
```

---

### 1.2 Advisor Module Panel Flow (`advisor`)
A functional interface focused on single-click, rapid-action execution.

```
+-------------------------------------------------------+
| 1. AUTENTICACIÓN Y SELECCIÓN DE MÓDULO (1-50)         |
+---------------------------+---------------------------+
                            |
                            v
+-------------------------------------------------------+
| 2. PANEL PRINCIPAL EN ESPERA (Botón Llamar)           |
+---------------------------+---------------------------+
                            | (Presiona "Llamar siguiente")
                            v
+-------------------------------------------------------+
| 3. ESTADO: CALLING (Ticket asignado)                  |
| - Botón "Iniciar atención" (Habilitado)               |
| - Botón "Re-llamar" (Habilitado)                      |
| - Botón "No se presentó" (Bloqueado 20s)              |
+-------------+---------------------------+-------------+
              |                           |
           (Inicia)             (No se presenta / Ausente)
              |                           |
              v                           v
+-----------------------+       +-----------------------+
| 4. ESTADO: IN_PROG.   |       | ESTADO: NO_SHOW       |
| - Timer 30 min activo |       | - Cierra ticket       |
| - Botón "Finalizar"   |       | - Vuelve a Espera     |
+-----------+-----------+       +-----------------------+
            |
    (Presiona "Finalizar")
            |
            v
+-----------------------+
| 5. ESTADO: COMPLETED  |
| - Retorna a Espera    |
+-----------------------+
```

---

### 1.3 Public Screen Display Layout (`display` - TV)
A high-visibility, passive view without touch interaction, dynamically split into two blocks:
* **Main Highlight Area (70% viewport):** Displays the active called ticket alongside a sound wave visualizer corresponding to the Text-to-Speech vocal readout.
* **Side Queue Grid (30% viewport):** Displays the last 10 called tickets remaining in `calling` state.

---

## 2. Interface Guidelines and Visual States

### 2.1 Advisory Timer Color Coding (30-Minute Consultation)
In the advisor panel (`advisor`), the countdown timer dynamically changes colors based on remaining time:

| Time State | Time Range | Tailwind CSS Class / Color | Operational Meaning |
| :--- | :--- | :--- | :--- |
| **Normal Time** | 30:00 to 10:00 | `bg-green-500` (`#22C55E`) | Consultation progressing within standard timeframe. |
| **Warning Time** | 09:59 to 03:00 | `bg-yellow-500` (`#EAB308`) | Visual warning approaching time limit. |
| **Critical / Overtime** | 02:59 to 00:00 (and negative counter) | `bg-red-500` (`#EF4444`) | Time limit exceeded. Timer flashes to alert advisor. |

---

### 2.2 Thermal Ticket Print Layout (80mm)
The 80mm thermal receipt format uses legible monospaced fonts and structured alignment:

```
=========================================================
                    EXPO ASESORÍAS 2026
=========================================================
Fecha: 20/08/2026                        Hora: 10:15 AM
Doc: DNI ***45678
---------------------------------------------------------


                    TURNO DE ATENCIÓN

                         FIN-00001


---------------------------------------------------------
Categoría: ASESORÍA FINANCIERA
Tipo: ATENCIÓN ESTÁNDAR
---------------------------------------------------------

       Por favor, atento a las pantallas
         públicas y al audio del recinto.

=========================================================
```

* **Preferential Variant:** If `is_priority = true`, the ticket code prefixes a `P-` (e.g., `P-FIN-00001`) and includes a bold highlighted banner reading: `*** ATENCIÓN PREFERENCIAL ***`.

---

### 2.3 Public Screen TV Layout (Turnero TV)
The responsive grid layout for Full HD resolution (1920x1080) follows this visual structure:

```
+-------------------------------------------------+---------------------------+
|                                                 |      ÚLTIMOS LLAMADOS     |
|               LOGOTIPO DEL EVENTO               | +-----------------------+ |
|                                                 | | FIN-00002  ->  MOD 02 | |
+-------------------------------------------------+ +-----------------------+ |
|                                                 |                           |
|                   TURNO ACTUAL                  | +-----------------------+ |
|                                                 | | LEG-00005  ->  MOD 12 | |
|                     FIN-00001                   | +-----------------------+ |
|                                                 |                           |
|                  PASE AL MÓDULO                 | +-----------------------+ |
|                                                 | | P-FIN-00003 -> MOD 01 | |
|                        05                       | +-----------------------+ |
|                                                 |                           |
|                                                 | +-----------------------+ |
|                                                 | | FIN-00004  ->  MOD 08 | |
| Categoría: Financiera                           | +-----------------------+ |
|                                                 |                           |
| [🔊] Reproduciendo aviso de voz...              | +-----------------------+ |
|                                                 | | MIG-00001  ->  MOD 15 | |
|                                                 | +-----------------------+ |
+-------------------------------------------------+---------------------------+
```

---

## 3. Touch and Interactive Component Specifications

### 3.1 Kiosk Components (`kiosk`)
* **On-Screen Numpad:** Touch targets set to $60 \times 60 \text{ px}$ with $12 \text{ px}$ inter-digit spacing to prevent mis-touches during document entry.
* **Action Feedback:** Visual active state scaling (`active:scale-95`) and large confirmation overlays upon ticket generation.

### 3.2 Advisor Panel Components (`advisor`)
* **Call Control Toolbar:** Three primary, color-differentiated action buttons:
  * **"Call Next" / "Llamar siguiente":** Primary action button (Blue).
  * **"Recall" / "Re-llamar":** Secondary action button (Gray with repeat icon).
  * **"Mark No-Show" / "No se presentó":** Danger action button (Red), overlaid with a 20-second countdown indicator while temporarily disabled.
* **Station Indicator:** Permanently displays the assigned module number (e.g., `"Módulo 05"`) and the currently logged-in advisor name.