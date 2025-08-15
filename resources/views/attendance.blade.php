{{-- resources/views/attendance.blade.php --}}
@extends('layouts.app')

@push('styles')
    {{-- Menggunakan Tailwind CSS & Chart.js dari CDN --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    {{-- Menambahkan Axios dari CDN --}}
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <style>
        /* Custom scrollbar for better aesthetics */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #1f2937; }
        ::-webkit-scrollbar-thumb { background: #4f46e5; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #6366f1; }
        /* Custom style for date picker icon */
        input[type="date"]::-webkit-calendar-picker-indicator { filter: invert(0.8); }
        /* Utility to hide scrollbar */
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
@endpush

@section('content')
<div class="bg-gray-900 text-gray-200 min-h-screen font-sans p-4 sm:p-6 lg:p-8">
    <div class="max-w-7xl mx-auto">

        {{-- HEADER --}}
        <div class="text-center mb-8">
            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 to-purple-500">
                Sistem Absensi Karyawan
            </h1>
            <p class="text-gray-400 mt-2">Kelola absensi dengan mudah dan efisien</p>
            <p id="live-time" class="text-indigo-400 mt-1 text-sm"></p>
        </div>

        {{-- Pesan Notifikasi --}}
        <div id="message-container" class="fixed top-5 right-5 z-50 transition-transform duration-500 translate-x-[200%]"></div>

        {{-- NAV TABS --}}
        <div class="mb-6">
            <div class="flex border-b border-gray-700 overflow-x-auto no-scrollbar">
                <button class="nav-tab active text-base py-3 px-4 sm:px-6 border-b-2 border-indigo-500 text-white font-semibold flex-shrink-0" onclick="showTab('attendance')">📝 Input Absensi</button>
                <button class="nav-tab text-base py-3 px-4 sm:px-6 text-gray-400 hover:text-white transition flex-shrink-0" onclick="showTab('recap')">📊 Rekap Bulanan</button>
                <button class="nav-tab text-base py-3 px-4 sm:px-6 text-gray-400 hover:text-white transition flex-shrink-0" onclick="showTab('employees')">👥 Kelola Karyawan</button>
                <button class="nav-tab text-base py-3 px-4 sm:px-6 text-gray-400 hover:text-white transition flex-shrink-0" onclick="showTab('statistics')">📈 Statistik</button>
            </div>
        </div>

        {{-- TAB CONTENT: INPUT ABSENSI --}}
        <div id="attendanceTab" class="tab-content">
            <div class="bg-gray-800 p-4 sm:p-6 rounded-lg shadow-lg">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 flex-wrap w-full sm:w-auto">
                        <div class="flex items-center gap-3">
                           <label for="attendance-date" class="font-semibold flex-shrink-0">Tanggal:</label>
                           <input type="date" id="attendance-date" class="bg-gray-700 border border-gray-600 rounded-md px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none w-full">
                        </div>
                        <input type="text" id="search-employee" oninput="filterEmployeeCards()" placeholder="Cari karyawan..." class="bg-gray-700 border border-gray-600 rounded-md px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none w-full sm:w-auto" />
                    </div>
                    <div class="flex items-center gap-3 w-full sm:w-auto">
                        <button onclick="markAllPresent()" class="w-1/2 sm:w-auto bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-md transition">Hadir Semua</button>
                        <button onclick="clearAllSelections()" class="w-1/2 sm:w-auto bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-md transition">Kosongkan</button>
                    </div>
                </div>
                <div id="attendanceGrid" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4"></div>
                <p id="no-employee-found" class="text-center text-gray-400 mt-8 hidden">Karyawan tidak ditemukan.</p>
                <button class="submit-btn w-full mt-8 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-lg transition flex items-center justify-center gap-2 text-lg" onclick="submitAttendance()">
                    <span id="submit-text">🚀 Kirim Absensi</span>
                </button>
            </div>
        </div>

        {{-- TAB CONTENT: REKAP BULANAN --}}
        <div id="recapTab" class="tab-content hidden">
            <div class="bg-gray-800 p-4 sm:p-6 rounded-lg shadow-lg">
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-4 mb-6 flex-wrap">
                    <select id="recap-month" class="w-full sm:w-auto bg-gray-700 border border-gray-600 rounded-md px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none"></select>
                    <select id="recap-year" class="w-full sm:w-auto bg-gray-700 border border-gray-600 rounded-md px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none"></select>
                    <button onclick="generateRecap()" class="w-full sm:w-auto bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-md transition">Tampilkan Rekap</button>
                    <button onclick="exportRecapToExcel()" id="exportButton" class="w-full sm:w-auto bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-md transition hidden">
                        Export ke Excel
                    </button>
                    <button onclick="deleteCurrentMonthData()" id="deleteMonthButton" class="w-full sm:w-auto bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-md transition hidden sm:ml-auto">
                        Hapus Data Bulan Ini
                    </button>
                </div>
                <div id="recapStatsContainer" class="mb-8"></div>
                <div id="recapTableContainer" class="mt-6"></div>
                <div id="crudControls" class="hidden mt-8 pt-6 border-t border-gray-700">
                    <button onclick="showAddForm()" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-md transition">➕ Tambah Data Manual</button>
                </div>
                <div id="crudFormContainer" class="hidden mt-6 bg-gray-700/50 p-6 rounded-lg">
                    <h3 id="form-title" class="text-xl font-semibold mb-4">Tambah Data Absensi</h3>
                    <form id="crudForm" onsubmit="event.preventDefault(); saveData();" class="space-y-4">
                        <div>
                            <label for="form-date" class="block mb-2 text-sm font-medium text-gray-300">Tanggal</label>
                            <input type="date" id="form-date" class="bg-gray-600 border border-gray-500 text-gray-200 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5" required>
                        </div>
                        <div>
                            <label for="form-employee" class="block mb-2 text-sm font-medium text-gray-300">Karyawan</label>
                            <select id="form-employee" class="bg-gray-600 border border-gray-500 text-gray-200 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5" required></select>
                        </div>
                        <div>
                            <label for="form-status" class="block mb-2 text-sm font-medium text-gray-300">Status</label>
                            <select id="form-status" class="bg-gray-600 border border-gray-500 text-gray-200 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5" required>
                                <option value="Hadir">Hadir</option>
                                <option value="Izin">Izin</option>
                                <option value="Sakit">Sakit</option>
                                <option value="Alpha">Alpha</option>
                            </select>
                        </div>
                        <div class="flex gap-4">
                            <button type="submit" class="w-full text-white bg-indigo-600 hover:bg-indigo-700 focus:ring-4 focus:outline-none focus:ring-indigo-800 font-medium rounded-lg text-sm px-5 py-2.5 text-center">Simpan</button>
                            <button type="button" onclick="cancelForm()" class="w-full text-white bg-gray-600 hover:bg-gray-700 focus:ring-4 focus:outline-none focus:ring-gray-800 font-medium rounded-lg text-sm px-5 py-2.5 text-center">Batal</button>
                        </div>
                    </form>
                </div>
                <div id="detailTableContainer" class="mt-8"></div>
            </div>
        </div>

        {{-- TAB CONTENT: KELOLA KARYAWAN --}}
        <div id="employeesTab" class="tab-content hidden">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="md:col-span-1">
                    <div class="bg-gray-800 p-4 sm:p-6 rounded-lg shadow-lg">
                        <h3 class="text-xl font-semibold text-white mb-4">Tambah Karyawan Baru</h3>
                        <form id="addEmployeeForm" onsubmit="event.preventDefault(); addEmployee();" class="space-y-4">
                            <div>
                                <label for="employee-name" class="block mb-2 text-sm font-medium text-gray-300">Nama Karyawan</label>
                                <input type="text" id="employee-name" class="bg-gray-700 border border-gray-600 text-gray-200 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5" placeholder="Contoh: Agung Setiawan" required>
                            </div>
                            <button type="submit" class="w-full text-white bg-indigo-600 hover:bg-indigo-700 focus:ring-4 focus:outline-none focus:ring-indigo-800 font-medium rounded-lg text-sm px-5 py-2.5 text-center">Tambah Karyawan</button>
                        </form>
                    </div>
                </div>
                <div class="md:col-span-2">
                    <div class="bg-gray-800 p-4 sm:p-6 rounded-lg shadow-lg">
                        <h3 class="text-xl font-semibold text-white mb-4">Daftar Karyawan</h3>
                        <div id="employeeListContainer" class="overflow-x-auto"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- TAB CONTENT: STATISTIK --}}
        <div id="statisticsTab" class="tab-content hidden">
            <div class="bg-gray-800 p-4 sm:p-6 rounded-lg shadow-lg">
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-4 mb-6 flex-wrap">
                    <select id="stats-month" class="w-full sm:w-auto bg-gray-700 border border-gray-600 rounded-md px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none"></select>
                    <select id="stats-year" class="w-full sm:w-auto bg-gray-700 border border-gray-600 rounded-md px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none"></select>
                    <button onclick="generateStatistics()" class="w-full sm:w-auto bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-md transition">Tampilkan Statistik</button>
                </div>
                <div id="stats-container" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <div class="lg:col-span-1 bg-gray-700/50 p-6 rounded-lg">
                        <h3 class="text-xl font-semibold text-white mb-4 text-center">Persentase Kehadiran</h3>
                        <canvas id="overallStatsChart"></canvas>
                    </div>
                    <div class="lg:col-span-2 bg-gray-700/50 p-6 rounded-lg">
                         <h3 class="text-xl font-semibold text-white mb-4 text-center">Tren Kehadiran Harian</h3>
                        <canvas id="dailyTrendChart"></canvas>
                    </div>
                </div>
                 <div id="no-stats-data" class="text-center text-gray-400 mt-8 hidden">
                    <p>Tidak ada data statistik untuk periode yang dipilih.</p>
                </div>
            </div>
        </div>

    </div>
</div>

{{-- DIALOG KONFIRMASI HAPUS --}}
<div id="confirm-dialog" class="fixed inset-0 z-50 items-center justify-center bg-black bg-opacity-60 hidden">
    <div class="bg-gray-800 rounded-lg shadow-xl p-6 w-full max-w-md mx-4">
        <p id="confirm-message" class="text-lg text-center text-gray-200">Apakah Anda yakin?</p>
        <div class="mt-6 flex justify-center gap-4">
            <button id="confirm-yes" class="px-6 py-2 rounded-lg bg-red-600 hover:bg-red-700 text-white font-semibold transition">Ya</button>
            <button id="confirm-no" class="px-6 py-2 rounded-lg bg-gray-600 hover:bg-gray-700 text-white font-semibold transition">Batal</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const API_URL = '{{ url("/api") }}';

    // *** PERBAIKAN UTAMA: KONFIGURASI AXIOS ***
    // Mengatur axios untuk mengirim cookie pada setiap permintaan.
    if (window.axios) {
        window.axios.defaults.withCredentials = true;
        window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
    }

    let employees = [];
    let currentEditingData = null;
    let overallChartInstance = null;
    let dailyChartInstance = null;

    function updateLiveTime() {
        const timeElement = document.getElementById('live-time');
        if (timeElement) {
            const now = new Date();
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            const dateString = now.toLocaleDateString('id-ID', options);
            const timeString = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' }).replace(/\./g, ':');
            timeElement.textContent = `${dateString} | ${timeString}`;
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const attendanceDateInput = document.getElementById('attendance-date');
        
        const today = new Date();
        const yyyy = today.getFullYear();
        const mm = String(today.getMonth() + 1).padStart(2, '0');
        const dd = String(today.getDate()).padStart(2, '0');
        attendanceDateInput.value = `${yyyy}-${mm}-${dd}`;

        attendanceDateInput.addEventListener('change', fetchAttendanceStatus);

        const recapMonthSelect = document.getElementById('recap-month');
        const recapYearSelect = document.getElementById('recap-year');
        const statsMonthSelect = document.getElementById('stats-month');
        const statsYearSelect = document.getElementById('stats-year');
        
        const currentDate = new Date();
        const currentYear = currentDate.getFullYear();
        const currentMonth = currentDate.getMonth() + 1;
        const monthNames = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
        
        for (let i = 0; i < 12; i++) {
            const option = document.createElement('option');
            option.value = i + 1;
            option.textContent = monthNames[i];
            recapMonthSelect.appendChild(option.cloneNode(true));
            statsMonthSelect.appendChild(option.cloneNode(true));
        }
        for (let i = currentYear; i >= currentYear - 5; i--) {
            const option = document.createElement('option');
            option.value = i;
            option.textContent = i;
            recapYearSelect.appendChild(option.cloneNode(true));
            statsYearSelect.appendChild(option.cloneNode(true));
        }
        
        recapMonthSelect.value = currentMonth;
        recapYearSelect.value = currentYear;
        statsMonthSelect.value = currentMonth;
        statsYearSelect.value = currentYear;
        
        refreshAllEmployeeData();
        generateStatistics();
        updateLiveTime();
        setInterval(updateLiveTime, 1000);
    });

    async function refreshAllEmployeeData() {
        try {
            const response = await axios.get(`${API_URL}/employees`);
            employees = response.data;
            generateEmployeeCards(employees);
            populateEmployeeDropdown(employees);
            displayEmployeeListTable(employees);
            // await fetchAttendanceStatus(); // DIKOMENTARI: Mencegah auto-load status saat halaman pertama kali dibuka
        } catch (error) {
            console.error('Failed to load employees:', error);
            showMessage('Gagal memuat daftar karyawan. Sesi Anda mungkin telah berakhir, silakan login kembali.', 'error');
        }
    }

    function populateEmployeeDropdown(employeeList) {
        const select = document.getElementById('form-employee');
        select.innerHTML = '<option value="" disabled selected>Pilih Karyawan</option>';
        employeeList.forEach(emp => {
            const option = document.createElement('option');
            option.value = emp.id;
            option.textContent = emp.name;
            select.appendChild(option);
        });
    }

    function generateEmployeeCards(employeeList) {
        const grid = document.getElementById('attendanceGrid');
        grid.innerHTML = '';
        employeeList.forEach(employee => {
            const card = document.createElement('div');
            card.className = 'employee-card-item bg-gray-700/50 rounded-lg p-4 shadow-md transition hover:bg-gray-700';
            card.dataset.id = employee.id;
            card.innerHTML = `
                <div class="employee-name-text font-bold text-lg text-white mb-3 pb-2 border-b border-gray-600">${employee.name}</div>
                <div class="grid grid-cols-2 gap-2">
                    ${[['Hadir', 'green'], ['Izin', 'yellow'], ['Sakit', 'purple'], ['Alpha', 'red']].map(([status, color]) => `
                        <div>
                            <input type="radio" id="emp-${employee.id}-${status}" name="status-${employee.id}" value="${status}" class="hidden peer">
                            <label for="emp-${employee.id}-${status}" class="block w-full text-center py-2 px-3 rounded-md cursor-pointer bg-gray-600 text-gray-300 transition peer-checked:ring-2 peer-checked:ring-offset-2 peer-checked:ring-offset-gray-800 peer-checked:ring-${color}-500 peer-checked:text-white peer-checked:bg-${color}-600">
                                ${status}
                            </label>
                        </div>
                    `).join('')}
                </div>
            `;
            grid.appendChild(card);
        });
        filterEmployeeCards();
    }

    async function submitAttendance() {
        const date = document.getElementById('attendance-date').value;
        if (!date) return showMessage('Pilih tanggal terlebih dahulu!', 'error');
        const submitBtn = document.querySelector('.submit-btn');
        const submitText = document.getElementById('submit-text');
        submitText.innerHTML = '<span class="loading animate-spin h-5 w-5 mr-3 border-2 border-white border-t-transparent rounded-full"></span>Mengirim...';
        submitBtn.disabled = true;
        const attendanceData = [];
        employees.forEach(employee => {
            const selectedStatus = document.querySelector(`input[name="status-${employee.id}"]:checked`);
            if (selectedStatus) {
                attendanceData.push({
                    tanggal: date,
                    employee_id: employee.id,
                    status: selectedStatus.value,
                });
            }
        });
        if (attendanceData.length === 0) {
            showMessage('Pilih minimal satu status karyawan!', 'error');
            submitText.innerHTML = '🚀 Kirim Absensi';
            submitBtn.disabled = false;
            return;
        }
        try {
            const response = await axios.post(`${API_URL}/attendance`, { data: attendanceData });
            showMessage(`✅ ${response.data.message}`, 'success');
            clearAllSelections();
        } catch (error) {
            const message = error.response?.data?.message || 'Gagal mengirim data.';
            showMessage(`❌ Gagal: ${message}`, 'error');
            console.error('Error:', error);
        } finally {
            submitText.innerHTML = '🚀 Kirim Absensi';
            submitBtn.disabled = false;
        }
    }

    async function generateRecap() {
        const month = document.getElementById('recap-month').value;
        const year = document.getElementById('recap-year').value;
        const deleteMonthBtn = document.getElementById('deleteMonthButton');

        try {
            const response = await axios.get(`${API_URL}/recap?month=${month}&year=${year}`);
            const data = response.data;
            displayRecap(data.recap, month, year);
            displayDetailData(data.detail);
            document.getElementById('crudControls').style.display = 'flex';
            document.getElementById('exportButton').classList.remove('hidden');
            deleteMonthBtn.classList.remove('hidden');
        } catch (error) {
            showMessage(`❌ Error: ${error.response?.data?.message || 'Gagal mengambil data rekap.'}`, 'error');
            document.getElementById('exportButton').classList.add('hidden');
            deleteMonthBtn.classList.add('hidden');
        }
    }

    function displayRecap(recapData, month, year) {
        const statsContainer = document.getElementById('recapStatsContainer');
        const tableContainer = document.getElementById('recapTableContainer');
        const monthNames = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
        const monthName = monthNames[parseInt(month) - 1];
        const title = `Rekapitulasi Absensi - ${monthName} ${year}`;
        const now = new Date();
        const timestampOptions = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        const dateString = now.toLocaleDateString('id-ID', timestampOptions);
        const timeString = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
        const generationTime = `Dicetak pada: ${dateString}, Pukul ${timeString}`;
        
        let totalHadir = 0, totalIzin = 0, totalSakit = 0, totalAlpha = 0;
        for (const name in recapData) {
            totalHadir += recapData[name].Hadir || 0;
            totalIzin += recapData[name].Izin || 0;
            totalSakit += recapData[name].Sakit || 0;
            totalAlpha += recapData[name].Alpha || 0;
        }
        const totalRecords = totalHadir + totalIzin + totalSakit + totalAlpha;

        statsContainer.innerHTML = `
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                <div class="bg-gray-700 p-4 rounded-lg text-center"><p class="text-sm text-gray-400">Total Absen</p><p class="text-2xl font-bold text-white">${totalRecords}</p></div>
                <div class="bg-green-800/50 border border-green-600 p-4 rounded-lg text-center"><p class="text-sm text-green-300">Total Hadir</p><p class="text-2xl font-bold text-white">${totalHadir}</p></div>
                <div class="bg-yellow-800/50 border border-yellow-600 p-4 rounded-lg text-center"><p class="text-sm text-yellow-300">Total Izin</p><p class="text-2xl font-bold text-white">${totalIzin}</p></div>
                <div class="bg-purple-800/50 border border-purple-600 p-4 rounded-lg text-center"><p class="text-sm text-purple-300">Total Sakit</p><p class="text-2xl font-bold text-white">${totalSakit}</p></div>
                <div class="bg-red-800/50 border border-red-600 p-4 rounded-lg text-center"><p class="text-sm text-red-300">Total Alpha</p><p class="text-2xl font-bold text-white">${totalAlpha}</p></div>
            </div>
        `;

        if (Object.keys(recapData).length === 0) {
            tableContainer.innerHTML = `<h3 class="text-2xl font-semibold text-white mb-1 mt-8">${title}</h3><p class="text-gray-400">Tidak ada data rekapitulasi untuk periode ini.</p>`;
            return;
        }

        let tableHTML = `
            <h3 class="text-2xl font-semibold text-white mb-1 mt-8">${title}</h3>
            <p class="text-sm text-gray-400 mb-4">${generationTime}</p>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-300">
                    <thead class="text-xs text-gray-300 uppercase bg-gray-700">
                        <tr>
                            <th scope="col" class="px-6 py-3">Nama Karyawan</th>
                            <th scope="col" class="px-6 py-3">Hadir</th>
                            <th scope="col" class="px-6 py-3">Izin</th>
                            <th scope="col" class="px-6 py-3">Sakit</th>
                            <th scope="col" class="px-6 py-3">Alpha</th>
                        </tr>
                    </thead>
                    <tbody>
        `;
        for (const name in recapData) {
            const counts = recapData[name];
            tableHTML += `
                <tr class="border-b border-gray-700 hover:bg-gray-700/50">
                    <th scope="row" class="px-6 py-4 font-medium text-white whitespace-nowrap">${name}</th>
                    <td class="px-6 py-4">${counts.Hadir || 0}</td>
                    <td class="px-6 py-4">${counts.Izin || 0}</td>
                    <td class="px-6 py-4">${counts.Sakit || 0}</td>
                    <td class="px-6 py-4">${counts.Alpha || 0}</td>
                </tr>
            `;
        }
        tableHTML += `</tbody></table></div>`;
        tableContainer.innerHTML = tableHTML;
    }

    function displayDetailData(detailData) {
        const container = document.getElementById('detailTableContainer');
        if (detailData.length === 0) {
            container.innerHTML = '<p class="text-gray-400 mt-4">Tidak ada data detail untuk periode ini.</p>';
            return;
        }
        let tableHTML = `
            <h3 class="text-2xl font-semibold text-white mb-4 mt-8 pt-6 border-t border-gray-700">Data Detail</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-300">
                    <thead class="text-xs text-gray-300 uppercase bg-gray-700">
                        <tr>
                            <th scope="col" class="px-6 py-3">Waktu Absen</th>
                            <th scope="col" class="px-6 py-3">Nama</th>
                            <th scope="col" class="px-6 py-3">Status</th>
                            <th scope="col" class="px-6 py-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
        `;
        const statusColors = { Hadir: 'bg-green-600', Izin: 'bg-yellow-500', Sakit: 'bg-purple-600', Alpha: 'bg-red-600' };
        detailData.forEach(record => {
            const displayDate = formatDetailDateTime(record.tanggal, record.updated_at);
            tableHTML += `
                <tr class="border-b border-gray-700 hover:bg-gray-700/50">
                    <td class="px-6 py-4">${displayDate}</td>
                    <th scope="row" class="px-6 py-4 font-medium text-white whitespace-nowrap">${record.employee.name}</th>
                    <td class="px-6 py-4"><span class="px-2 py-1 text-xs font-semibold rounded-full text-white ${statusColors[record.status] || 'bg-gray-500'}">${record.status}</span></td>
                    <td class="px-6 py-4 flex gap-2">
                        <button class="font-medium text-blue-400 hover:underline" onclick='editData(${JSON.stringify(record)})'>Edit</button>
                        <button class="font-medium text-red-400 hover:underline" onclick='deleteData(${record.id})'>Hapus</button>
                    </td>
                </tr>
            `;
        });
        tableHTML += `</tbody></table></div>`;
        container.innerHTML = tableHTML;
    }
    
    function showTab(tabName) {
        document.querySelectorAll('.tab-content').forEach(tab => tab.classList.add('hidden'));
        document.getElementById(tabName + 'Tab').classList.remove('hidden');
        document.querySelectorAll('.nav-tab').forEach(tab => {
            tab.classList.remove('active', 'border-indigo-500', 'text-white');
            tab.classList.add('text-gray-400');
        });
        const activeTab = document.querySelector(`.nav-tab[onclick="showTab('${tabName}')"]`);
        activeTab.classList.add('active', 'border-indigo-500', 'text-white');
    }

    function clearAllSelections() {
        document.querySelectorAll('input[type="radio"]').forEach(radio => radio.checked = false);
    }

    function markAllPresent() {
        employees.forEach(employee => {
            const presentRadio = document.getElementById(`emp-${employee.id}-Hadir`);
            if (presentRadio) presentRadio.checked = true;
        });
    }

    function showAddForm() {
        currentEditingData = null;
        document.getElementById('form-title').textContent = 'Tambah Data Absensi';
        document.getElementById('crudForm').reset();
        document.getElementById('form-employee').disabled = false;
        document.getElementById('crudFormContainer').classList.remove('hidden');
    }
    
    function editData(record) {
        currentEditingData = record;
        document.getElementById('form-title').textContent = 'Edit Data Absensi';
        document.getElementById('form-date').value = record.tanggal;
        document.getElementById('form-employee').value = record.employee_id;
        document.getElementById('form-employee').disabled = true;
        document.getElementById('form-status').value = record.status;
        document.getElementById('crudFormContainer').classList.remove('hidden');
    }

    function cancelForm() {
        document.getElementById('crudFormContainer').classList.add('hidden');
        document.getElementById('crudForm').reset();
        currentEditingData = null;
    }

    async function saveData() {
        const record = {
            tanggal: document.getElementById('form-date').value,
            employee_id: document.getElementById('form-employee').value,
            status: document.getElementById('form-status').value,
        };
        
        try {
            let response;
            if (currentEditingData) {
                response = await axios.put(`${API_URL}/attendance-data/${currentEditingData.id}`, record);
            } else {
                response = await axios.post(`${API_URL}/attendance-data`, record);
            }
            showMessage(response.data.message, 'success');
            cancelForm();
            refreshData();
        } catch(error) {
            showMessage(`❌ Gagal menyimpan data: ${error.response?.data?.message || 'Terjadi kesalahan.'}`, 'error');
        }
    }

    function deleteData(recordId) {
        showConfirmDialog('Apakah Anda yakin ingin menghapus data ini?', () => confirmDelete(recordId));
    }
    
    async function confirmDelete(recordId) {
        try {
            const response = await axios.delete(`${API_URL}/attendance-data/${recordId}`);
            showMessage(response.data.message, 'success');
            refreshData();
        } catch (error) {
            showMessage(`❌ Gagal menghapus: ${error.response?.data?.message || 'Gagal menghapus data.'}`, 'error');
        }
    }
    
    function refreshData() {
        if (!document.getElementById('recapTab').classList.contains('hidden')) {
            generateRecap();
        }
    }

    // FUNGSI BARU UNTUK MEMULAI PROSES HAPUS PER BULAN
    function deleteCurrentMonthData() {
        const month = document.getElementById('recap-month').value;
        const year = document.getElementById('recap-year').value;
        const monthNames = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
        const monthName = monthNames[parseInt(month) - 1];

        const message = `Apakah Anda yakin ingin menghapus SEMUA data absensi untuk bulan ${monthName} ${year}? Aksi ini tidak dapat dibatalkan.`;
        
        showConfirmDialog(message, () => confirmDeleteMonth(month, year));
    }

    // FUNGSI BARU UNTUK MENGIRIM PERMINTAAN HAPUS PER BULAN
    async function confirmDeleteMonth(month, year) {
        try {
            const response = await axios.delete(`${API_URL}/recap/delete-month`, {
                data: { month, year } // Untuk axios, body request DELETE ada di properti 'data'
            });
            
            showMessage(response.data.message, 'success');
            generateRecap(); // Muat ulang data rekap setelah berhasil dihapus
        } catch (error) {
            showMessage(`❌ Gagal menghapus: ${error.response?.data?.message || 'Gagal menghapus data.'}`, 'error');
        }
    }

    function showMessage(msg, type = 'success') {
        const container = document.getElementById('message-container');
        const bgColor = type === 'success' ? 'bg-green-500' : 'bg-red-500';
        container.innerHTML = `<div class="px-6 py-3 rounded-lg shadow-lg text-white ${bgColor}">${msg}</div>`;
        container.classList.remove('translate-x-[200%]');
        container.classList.add('translate-x-0');
        setTimeout(() => {
            container.classList.remove('translate-x-0');
            container.classList.add('translate-x-[200%]');
        }, 3000);
    }
    
    function formatDetailDateTime(dateString, timeString) {
        const attendanceDate = new Date(dateString);
        const updateTime = new Date(timeString);
        if (isNaN(attendanceDate.getTime()) || isNaN(updateTime.getTime())) return 'Waktu tidak valid';
        const dateOptions = { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' };
        const timeOptions = { hour: '2-digit', minute: '2-digit', hour12: false };
        const formattedDate = attendanceDate.toLocaleDateString('id-ID', dateOptions);
        const formattedTime = updateTime.toLocaleTimeString('id-ID', timeOptions).replace(/\./g, ':');
        return `${formattedDate}, Pukul ${formattedTime}`;
    }
    
    function showConfirmDialog(message, onConfirm) {
        const dialog = document.getElementById('confirm-dialog');
        document.getElementById('confirm-message').textContent = message;
        const confirmBtn = document.getElementById('confirm-yes');
        const cancelBtn = document.getElementById('confirm-no');
        const confirmHandler = () => {
            dialog.classList.add('hidden');
            onConfirm();
            confirmBtn.removeEventListener('click', confirmHandler);
            cancelBtn.removeEventListener('click', cancelHandler);
        };
        const cancelHandler = () => {
            dialog.classList.add('hidden');
            confirmBtn.removeEventListener('click', confirmHandler);
            cancelBtn.removeEventListener('click', cancelHandler);
        };
        confirmBtn.addEventListener('click', confirmHandler);
        cancelBtn.addEventListener('click', cancelHandler);
        dialog.classList.remove('hidden');
        dialog.classList.add('flex');
    }

    function displayEmployeeListTable(employeeList) {
        const container = document.getElementById('employeeListContainer');
        if (employeeList.length === 0) {
            container.innerHTML = '<p class="text-gray-400">Belum ada karyawan yang ditambahkan.</p>';
            return;
        }
        let tableHTML = `
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-300">
                    <thead class="text-xs text-gray-300 uppercase bg-gray-700">
                        <tr>
                            <th scope="col" class="px-6 py-3">Nama Karyawan</th>
                            <th scope="col" class="px-6 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
        `;
        employeeList.forEach(emp => {
            tableHTML += `
                <tr class="border-b border-gray-700 hover:bg-gray-700/50">
                    <th scope="row" class="px-6 py-4 font-medium text-white whitespace-nowrap">${emp.name}</th>
                    <td class="px-6 py-4 text-right">
                        <button class="font-medium text-red-400 hover:underline" onclick="deleteEmployee(${emp.id}, '${emp.name}')">Hapus</button>
                    </td>
                </tr>
            `;
        });
        tableHTML += `</tbody></table></div>`;
        container.innerHTML = tableHTML;
    }

    async function addEmployee() {
        const nameInput = document.getElementById('employee-name');
        const name = nameInput.value.trim();
        if (!name) {
            return showMessage('Nama karyawan tidak boleh kosong!', 'error');
        }
        try {
            const response = await axios.post(`${API_URL}/employees`, { name: name });
            const result = response.data;
            showMessage(`✅ ${result.message}`, 'success');
            nameInput.value = '';
            refreshAllEmployeeData();
        } catch (error) {
            const errorMessage = error.response?.data?.errors?.name?.[0] || error.response?.data?.message || 'Gagal menambahkan karyawan.';
            showMessage(`❌ Gagal: ${errorMessage}`, 'error');
        }
    }

    function deleteEmployee(employeeId, employeeName) {
        showConfirmDialog(`Apakah Anda yakin ingin menghapus karyawan "${employeeName}"? Semua data absensinya juga akan terhapus.`, () => {
            confirmDeleteEmployee(employeeId);
        });
    }

    async function confirmDeleteEmployee(employeeId) {
        try {
            const response = await axios.delete(`${API_URL}/employees/${employeeId}`);
            showMessage(response.data.message, 'success');
            refreshAllEmployeeData();
        } catch (error) {
            showMessage(`❌ Gagal menghapus: ${error.response?.data?.message || 'Gagal menghapus karyawan.'}`, 'error');
        }
    }

    function filterEmployeeCards() {
        const searchTerm = document.getElementById('search-employee').value.toLowerCase();
        const cards = document.querySelectorAll('.employee-card-item');
        let visibleCount = 0;
        cards.forEach(card => {
            const name = card.querySelector('.employee-name-text').textContent.toLowerCase();
            if (name.includes(searchTerm)) {
                card.style.display = '';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });
        const noResultEl = document.getElementById('no-employee-found');
        if (visibleCount === 0) {
            noResultEl.classList.remove('hidden');
        } else {
            noResultEl.classList.add('hidden');
        }
    }

    function exportRecapToExcel() {
        const month = document.getElementById('recap-month').value;
        const year = document.getElementById('recap-year').value;
        const url = `{{ url('/api/export/recap') }}?month=${month}&year=${year}`;
        window.open(url, '_blank');
    }

    async function generateStatistics() {
        const month = document.getElementById('stats-month').value;
        const year = document.getElementById('stats-year').value;
        try {
            const response = await axios.get(`${API_URL}/statistics?month=${month}&year=${year}`);
            const data = response.data;
            if (Object.keys(data.overall).length === 0) {
                document.getElementById('stats-container').classList.add('hidden');
                document.getElementById('no-stats-data').classList.remove('hidden');
                if (overallChartInstance) overallChartInstance.destroy();
                if (dailyChartInstance) dailyChartInstance.destroy();
                return;
            }
            document.getElementById('stats-container').classList.remove('hidden');
            document.getElementById('no-stats-data').classList.add('hidden');
            renderOverallStatsChart(data.overall);
            renderDailyTrendChart(data.daily_trend);
        } catch (error) {
            showMessage(`❌ Error: ${error.response?.data?.message || 'Gagal mengambil data statistik.'}`, 'error');
        }
    }

    function renderOverallStatsChart(data) {
        const ctx = document.getElementById('overallStatsChart').getContext('2d');
        if (overallChartInstance) {
            overallChartInstance.destroy();
        }
        const labels = Object.keys(data);
        const values = Object.values(data);
        const chartData = {
            labels: labels,
            datasets: [{
                label: 'Total',
                data: values,
                backgroundColor: [
                    'rgba(34, 197, 94, 0.7)',
                    'rgba(234, 179, 8, 0.7)',
                    'rgba(139, 92, 246, 0.7)',
                    'rgba(239, 68, 68, 0.7)'
                ],
                borderColor: '#1f2937',
                borderWidth: 3
            }]
        };
        overallChartInstance = new Chart(ctx, {
            type: 'doughnut',
            data: chartData,
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: { color: '#d1d5db' }
                    }
                }
            }
        });
    }

    function renderDailyTrendChart(data) {
        const ctx = document.getElementById('dailyTrendChart').getContext('2d');
        if (dailyChartInstance) {
            dailyChartInstance.destroy();
        }
        const labels = Object.keys(data);
        const values = Object.values(data);
        const chartData = {
            labels: labels,
            datasets: [{
                label: 'Jumlah Karyawan Hadir',
                data: values,
                fill: true,
                borderColor: 'rgb(99, 102, 241)',
                backgroundColor: 'rgba(99, 102, 241, 0.2)',
                tension: 0.3
            }]
        };
        dailyChartInstance = new Chart(ctx, {
            type: 'line',
            data: chartData,
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { color: '#9ca3af', stepSize: 1 },
                        grid: { color: '#374151' }
                    },
                    x: {
                        ticks: { color: '#9ca3af' },
                         grid: { color: '#374151' }
                    }
                },
                plugins: {
                    legend: { display: false }
                }
            }
        });
    }

    async function fetchAttendanceStatus() {
        const date = document.getElementById('attendance-date').value;
        if (!date) return;

        clearAllSelections();

        try {
            const response = await axios.get(`${API_URL}/attendance-status?date=${date}`);
            const statuses = response.data;
            
            if (Object.keys(statuses).length === 0) {
                return;
            }
            for (const employeeId in statuses) {
                const status = statuses[employeeId];
                const radio = document.getElementById(`emp-${employeeId}-${status}`);
                if (radio) {
                    radio.checked = true;
                }
            }
        } catch (error) {
            console.error('Error fetching attendance status:', error);
            // Tidak menampilkan pesan error di sini agar tidak mengganggu jika hanya data status yang gagal
        }
    }
</script>
@endpush
