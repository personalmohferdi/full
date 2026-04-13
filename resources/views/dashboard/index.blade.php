@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <!-- Hero Card -->
                <div class="card border-0 shadow-sm overflow-hidden" style="border-radius: 15px;">
                    <div class="card-body p-0">
                        <div style="background: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), url('{{ asset('assets/img/wk.jpg') }}'); 
                                                background-size: cover; background-position: center; height: 300px;"
                            class="d-flex align-items-center px-5 text-white">
                            <div>
                                <h1 class="display-5 fw-bold">Welcome Back, {{ auth()->user()->name }}</h1>
                                <p class="lead opacity-75">Sistem Manajemen Inventaris SMK Wikrama Bogor.</p>
                                <hr class="w-25 border-2 opacity-100 mt-4">
                                <p class="small">Pilih menu di sidebar untuk mulai mengelola data.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm p-4" style="border-radius: 15px;">
                    <h5 class="fw-bold mb-3">Total Stock per Category</h5>
                    <canvas id="chartStockByCategory" height="120"></canvas>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm p-4" style="border-radius: 15px;">
                    <h5 class="fw-bold mb-3">Stock Condition</h5>
                    <canvas id="chartCondition" height="220"></canvas>
                </div>
            </div>
        </div>

    </div>
@endsection

@push('script')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const labels = @json($labels);
            const stocks = @json($stocks);

            // Bar chart
            new Chart(document.getElementById('chartStockByCategory'), {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Total Stock',
                        data: stocks,
                        backgroundColor: '#2563eb'
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false } }
                }
            });

            // Doughnut chart: Repair vs Good
            const totalStock = @json($totalStock);
            const totalRepair = @json($totalRepair);
            const good = Math.max(totalStock - totalRepair, 0);

            new Chart(document.getElementById('chartCondition'), {
                type: 'doughnut',
                data: {
                    labels: ['Good', 'Repair'],
                    datasets: [{
                        data: [good, totalRepair],
                        backgroundColor: ['#16a34a', '#f59e0b']
                    }]
                },
                options: {
                    responsive: true
                }
            });
        });
    </script>
@endpush