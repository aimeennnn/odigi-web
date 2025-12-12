// Dashboard JavaScript - Chart.js Implementation
// Grafik Rekapitulasi Permintaan Pengajuan

// Function to initialize dashboard chart
function initDashboardChart(grafikData) {
    const ctx = document.getElementById("grafikPengajuan").getContext("2d");

    const chart = new Chart(ctx, {
        type: "line",
        data: {
            labels: grafikData.labels,
            datasets: [
                {
                    label: "Permintaan Pengajuan",
                    data: grafikData.data,
                    borderColor: "#4f8cff",
                    backgroundColor: "rgba(79,140,255,0.1)",
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: "#4f8cff",
                    pointBorderColor: "#fff",
                    pointBorderWidth: 2,
                    pointRadius: 6,
                },
            ],
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false,
                },
                tooltip: {
                    callbacks: {
                        title: function (context) {
                            const index = context[0].dataIndex;
                            const dataPoint = grafikData.rawData[index];
                            const namaHari = dataPoint.nama_hari;
                            const tanggal = dataPoint.tanggal;
                            const indexHari = dataPoint.index;

                            if (indexHari == 0) {
                                return (
                                    "Hari ke-0: " +
                                    namaHari +
                                    " (" +
                                    tanggal +
                                    ")"
                                );
                            } else {
                                return context[0].label + " (" + tanggal + ")";
                            }
                        },
                        label: function (context) {
                            return "Total: " + context.parsed.y + " pengajuan";
                        },
                    },
                },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                        callback: function (value) {
                            return Math.floor(value);
                        },
                    },
                },
                x: {
                    ticks: {
                        maxRotation: 0,
                        minRotation: 0,
                    },
                },
            },
        },
    });

    return chart;
}

// Initialize chart when DOM is ready
document.addEventListener("DOMContentLoaded", function () {
    // Check if chart canvas exists
    const chartCanvas = document.getElementById("grafikPengajuan");
    if (chartCanvas && typeof window.dashboardChartData !== "undefined") {
        initDashboardChart(window.dashboardChartData);
    }
});
