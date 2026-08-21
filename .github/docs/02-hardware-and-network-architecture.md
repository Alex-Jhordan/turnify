# 02. LAN, Hardware, and Infrastructure Architecture

## 1. On-Premises Local Area Network (LAN) Deployment

To guarantee zero latency, high availability, and eliminate dependencies on cellular networks or public internet connectivity during the event, the software is deployed within an isolated local area network (LAN).

### Network Topology Schema

```
+-------------------------------------------------------+
|                 Servidor Local Principal              |
|        (Laragon / Docker + Laravel)                   |
|              IP: 192.168.1.100:8000                   |
+---------------------------+---------------------------+
                            |
                            |
+---------------------------+---------------------------+
|                   Switch / Router LAN                 |
|                   (Red sin Internet)                  |
+------+--------------------+--------------------+------+
       |                    |                    |
       |                    |                    |
+------+------+      +------+------+      +------+------+
| Tóte. reg.  |      | Laptops mód.|      | PantallasTV |
| (3-5 unid.) |      | (50 disp.)  |      | (2-4 unid.) |
| 192.168.1.10X      | 192.168.1.1XX      | 192.168.1.20X
+-------------+      +-------------+      +-------------+
```

### 1.1 Local Main Server
* **Infrastructure:** High-performance desktop PC physically located in the event control center.
* **Runtime Environment:** Local web server stack running on Laragon (Linux/Windows architecture).
* **Hosted Services:**
  * **Web Server:** Nginx / Apache listening on static IP assignment (e.g., `192.168.1.100`).
  * **Database Engine:** MySQL 8.x.
  * **WebSocket Server:** Real-time event broadcasting via Laravel Reverb listening on dedicated port (e.g., `6001`).

### 1.2 Network Topology
* **Wired Connection (Ethernet):** Recommended for Main Server PC, Kiosk registration terminals, and dedicated display laptops.
* **Private/Dedicated Wi-Fi Network:** High-density Wi-Fi Router access with hidden SSID and private access credentials exclusively dedicated to the 50 advisor laptops.

---

## 2. Hardware Specification and Integration

### 2.1 Registration Kiosks (`kiosk`)
* **Device:** Touchscreen display with embedded Chromium web browser configured in Kiosk mode (`--kiosk --kiosk-printing`).
* **Access Point:** Auto-boots directly to kiosk URL (`http://192.168.1.100/kiosk`).
* **Peripherals:** Thermal receipt printer connected via USB interface.

### 2.2 Thermal Printers & Silent Printing
To prevent operating system print dialog popups during high-volume ticket issuance, printing is controlled via native browser directives:
* **Printing Protocol:** Native browser directive in Kiosk mode. Web application triggers JavaScript `window.print()` upon ticket generation confirmation.
* **Paper Format:** Standard 80mm thermal paper roll.
* **Printed Ticket Layout:**
  * Event Name / Logo.
  * Ticket Code in large prominent font (e.g., `FIN-001` or `P-FIN-001` for priority tickets).
  * Selected Category Name.
  * Exact timestamp (`created_at`) and tolerance warning note.

### 2.3 Advisor Laptops (`advisor`)
* **Device:** Laptops connected to LAN via Ethernet or private Wi-Fi.
* **Access Point:** Modern web browser accessing Filament/Livewire panel (`http://192.168.1.100/advisor`).
* **Display Requirement:** Minimum HD resolution (1280x720) for optimal panel layout and countdown timer visibility.

### 2.4 Public Display Screens (`display`)
* **Device:** Large-format Smart TVs or monitors connected via HDMI to dedicated mini-PCs or laptops.
* **Access Point:** Full-screen browser window pointed to public display URL (`http://192.168.1.100/display`).
* **Audio Requirement:** Enabled speakers or audio-out interface on laptop/TV for voice call alerts via client-side Web Speech API (Text-to-Speech).

---

## 3. Contingency and Resilience Plan

### 3.1 Printer Failure or Paper Exhaustion at Kiosk
* **Detection:** Print interface error events or support staff reports.
* **Fallback Mechanism:** Kiosk screen renders an on-screen confirmation card containing the generated ticket code alongside a dynamically rendered QR code. Attendees can photograph the screen with a mobile device while paper replacement is performed.

### 3.2 Advisor Laptop Disconnection or Hardware Failure
* **Impact:** Zero impact on the database or global virtual queue.
* **Recovery Mechanism:** Advisor relocates to any backup laptop on the LAN, authenticates with their credentials, selects their physical module number, and resumes their active session state seamlessly.

### 3.3 Power Outage at Venue
* **Server Mitigation:** Uninterruptible Power Supply (UPS / SAI) connected to Main Server PC and core network equipment (Switch/Router).
* **Data Recovery:** All ticket state changes are committed directly to MySQL with immediate persistence. Upon network restoration, display screens and advisor devices automatically reconnect to the Laravel Reverb WebSocket server.