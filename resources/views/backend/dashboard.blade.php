@extends('backend.include.layout')
@section('content')
    <br>
    <!-- Example Colored Cards for Dashboard Demo-->
    <div class="row">
        <div class="col-lg-6 col-xl-3 mb-4">
            <div class="card bg-primary text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="me-3">
                            <div class="text-white-75 small">Earnings (Monthly)</div>
                            <div class="text-lg fw-bold">$40,000</div>
                        </div>
                        <i class="feather-xl text-white-50" data-feather="calendar"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-xl-3 mb-4">
            <div class="card bg-warning text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="me-3">
                            <div class="text-white-75 small">Earnings (Annual)</div>
                            <div class="text-lg fw-bold">$215,000</div>
                        </div>
                        <i class="feather-xl text-white-50" data-feather="dollar-sign"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-xl-3 mb-4">
            <div class="card bg-success text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="me-3">
                            <div class="text-white-75 small">Task Completion</div>
                            <div class="text-lg fw-bold">24</div>
                        </div>
                        <i class="feather-xl text-white-50" data-feather="check-square"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-xl-3 mb-4">
            <div class="card bg-danger text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="me-3">
                            <div class="text-white-75 small">Pending Requests</div>
                            <div class="text-lg fw-bold">17</div>
                        </div>
                        <i class="feather-xl text-white-50" data-feather="message-circle"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-xl-3 mb-4">
            <div class="card bg-primary text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="me-3">
                            <div class="text-white-75 small">Earnings (Monthly)</div>
                            <div class="text-lg fw-bold">$40,000</div>
                        </div>
                        <i class="feather-xl text-white-50" data-feather="calendar"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-xl-3 mb-4">
            <div class="card bg-warning text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="me-3">
                            <div class="text-white-75 small">Earnings (Annual)</div>
                            <div class="text-lg fw-bold">$215,000</div>
                        </div>
                        <i class="feather-xl text-white-50" data-feather="dollar-sign"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-xl-3 mb-4">
            <div class="card bg-success text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="me-3">
                            <div class="text-white-75 small">Task Completion</div>
                            <div class="text-lg fw-bold">24</div>
                        </div>
                        <i class="feather-xl text-white-50" data-feather="check-square"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-xl-3 mb-4">
            <div class="card bg-danger text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="me-3">
                            <div class="text-white-75 small">Pending Requests</div>
                            <div class="text-lg fw-bold">17</div>
                        </div>
                        <i class="feather-xl text-white-50" data-feather="message-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Example Charts for Dashboard Demo-->
    <div class="row">
        <div class="col-xl-6 mb-4">
            <div class="card card-header-actions h-100">
                <div class="card-header">
                    Earnings Breakdown
                    <div class="dropdown no-caret">
                        <button class="btn btn-transparent-dark btn-icon dropdown-toggle" id="areaChartDropdownExample"
                            type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i
                                class="text-gray-500" data-feather="more-vertical"></i></button>
                        <div class="dropdown-menu dropdown-menu-end animated--fade-in-up"
                            aria-labelledby="areaChartDropdownExample">
                            <a class="dropdown-item" href="dashboard-1.html#!">Last 12 Months</a>
                            <a class="dropdown-item" href="dashboard-1.html#!">Last 30 Days</a>
                            <a class="dropdown-item" href="dashboard-1.html#!">Last 7 Days</a>
                            <a class="dropdown-item" href="dashboard-1.html#!">This Month</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="dashboard-1.html#!">Custom Range</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-area"><canvas id="myAreaChart" width="100%" height="30"></canvas></div>
                </div>
            </div>
        </div>
        <div class="col-xl-6 mb-4">
            <div class="card card-header-actions h-100">
                <div class="card-header">
                    Monthly Revenue
                    <div class="dropdown no-caret">
                        <button class="btn btn-transparent-dark btn-icon dropdown-toggle" id="areaChartDropdownExample"
                            type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i
                                class="text-gray-500" data-feather="more-vertical"></i></button>
                        <div class="dropdown-menu dropdown-menu-end animated--fade-in-up"
                            aria-labelledby="areaChartDropdownExample">
                            <a class="dropdown-item" href="dashboard-1.html#!">Last 12 Months</a>
                            <a class="dropdown-item" href="dashboard-1.html#!">Last 30 Days</a>
                            <a class="dropdown-item" href="dashboard-1.html#!">Last 7 Days</a>
                            <a class="dropdown-item" href="dashboard-1.html#!">This Month</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="dashboard-1.html#!">Custom Range</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-bar"><canvas id="myBarChart" width="100%" height="30"></canvas></div>
                </div>
            </div>
        </div>
    </div>
    <!-- Example DataTable for Dashboard Demo-->
    <div class="card mb-4">
        <div class="card-header">Personnel Management</div>
        <div class="card-body">
            <table id="datatablesSimple">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Position</th>
                        <th>Office</th>
                        <th>Age</th>
                        <th>Start date</th>
                        <th>Salary</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <th>Name</th>
                        <th>Position</th>
                        <th>Office</th>
                        <th>Age</th>
                        <th>Start date</th>
                        <th>Salary</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </tfoot>
                <tbody>
                    <tr>
                        <td>Tiger Nixon</td>
                        <td>System Architect</td>
                        <td>Edinburgh</td>
                        <td>61</td>
                        <td>2011/04/25</td>
                        <td>$320,800</td>
                        <td>
                            <div class="badge bg-primary text-white rounded-pill">Full-time</div>
                        </td>
                        <td>
                            <button class="btn btn-datatable btn-icon btn-transparent-dark me-2"><i
                                    class="fa-solid fa-ellipsis-vertical"></i></button>
                            <button class="btn btn-datatable btn-icon btn-transparent-dark"><i
                                    class="fa-regular fa-trash-can"></i></button>
                        </td>
                    </tr>
                    <tr>
                        <td>Donna Snider</td>
                        <td>Customer Support</td>
                        <td>New York</td>
                        <td>27</td>
                        <td>2011/01/25</td>
                        <td>$112,000</td>
                        <td>
                            <div class="badge bg-secondary text-white rounded-pill">Part-time</div>
                        </td>
                        <td>
                            <button class="btn btn-datatable btn-icon btn-transparent-dark me-2"><i
                                    class="fa-solid fa-ellipsis-vertical"></i></button>
                            <button class="btn btn-datatable btn-icon btn-transparent-dark"><i
                                    class="fa-regular fa-trash-can"></i></button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
@endsection
@section('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.min.js" crossorigin="anonymous"></script>
    <script src="{{ URL::asset('backend') }}/js/chart-area-demo.js"></script>
    <script src="{{ URL::asset('backend') }}/js/chart-bar-demo.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js"
        crossorigin="anonymous"></script>
    <script src="{{ URL::asset('backend') }}/js/datatables/datatables-simple-demo.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/litepicker/dist/bundle.js" crossorigin="anonymous"></script>
    <script src="{{ URL::asset('backend') }}/js/litepicker.js"></script>
@endsection
