EVENTAURA – EVENT & WEDDING MANAGEMENT ERP
============================================

REQUIREMENTS
- Windows PC
- XAMPP installed
- Apache module only (MySQL is NOT used)
- PHP included with XAMPP

INSTALLATION
1. Extract the Event_Wedding_ERP folder.
2. Copy the folder named Event_Wedding_ERP into:
   C:\xampp\htdocs\
3. Open XAMPP Control Panel.
4. Click Start next to Apache only.
5. Open this URL in your browser:
   http://localhost/Event_Wedding_ERP/

NO DATABASE SETUP
- This project does not use MySQL, SQL, phpMyAdmin, or an external database.
- Records are stored as JSON files inside the data folder.
- Keep the data folder writable.
- Do not delete the JSON files if you want to keep your records.

MODULES
- Dashboard
- Clients (Create, Read, Update, Delete, Search)
- Events (Create, Read, Update, Delete, Search)
- Venues (Create, Read, Update, Delete, Search)
- Vendors (Create, Read, Update, Delete, Search)
- Bookings (Create, Read, Update, Delete, Search)
- Payments (Create, Read, Update, Delete, Search)

IMPORTANT FOR COLLEGE ASSIGNMENT
The supplied BCA assignment guideline requires a database and SQL file. This
Apache-only JSON version is runnable without MySQL, but JSON storage does not
satisfy the assignment's stated database/SQL requirement. Confirm with your
faculty before submitting this version.

TROUBLESHOOTING
- If localhost does not open, ensure Apache is green/running in XAMPP.
- If Apache cannot start, another application may be using port 80/443.
- If records do not save, check that the data folder is writable.
- Use http://localhost/Event_Wedding_ERP/ (not file://).
