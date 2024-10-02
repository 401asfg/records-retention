@extends('layout')

@section('title', 'Records Retention Form')

@section('content')

<h1 class="text-center">Records Retention Form</h1>
<h4 class="text-center">Info Header</h4>

<form action="retention-requests" method="post" class="container mt-4">
    <div class="row">
        <div class="col-sm-6 col-12 mt-3">
            <label for="department_name" class="row"><strong>Department Name</strong></label>
            <input type="text" name="department_name" id="department_name" class="w-100" />
        </div>
        <div class="col-sm-6 col-12 mt-3">
            <label for="manager_name" class="row"><strong>Manager's Name</strong></label>
            <input type="text" name="manager_name" id="manager_name" class="w-100" />
        </div>
    </div>
    <div class="row">
        <div class="col-sm-6 col-12 mt-3">
            <label for="requestor_name" class="row"><strong>Completed By</strong></label>
            <input type="text" name="requestor_name" id="requestor_name" class="w-100" />
        </div>
        <div class="col-sm-6 col-12 mt-3">
            <label for="requestor_email" class="row"><strong>Email</strong></label>
            <input type="email" name="requestor_email" id="requestor_email" class="w-100" />
        </div>
    </div>
    <div class="row mt-5 justify-content-center">
        <h3 class="text-center">Boxes</h3>
        <div class="col-md-6 col-11">
            <div class="row border p-2">
                <div class="col-12">
                    <div><strong>Description</strong></div>
                    <textarea name="description_0" class="w-100" style="height: 100px;"></textarea>
                </div>
                <div class="col-lg-6 col-12 mt-1">
                    <div><strong>Final Disposition</strong></div>
                    <label for="shred_0"><input type="radio" name="final_disposition_0" id="shred_0"> Shred</label>
                    <label for="permanant_storage_0"><input type="radio" name="final_disposition_0" id="permanant_storage_0" /> Permanant Storage</label>
                </div>
                <div class="col-lg-6 col-12 mt-1">
                    <div class="row">
                        <label for="destroy_date_0"><strong>Destroy Date</strong></label>
                    </div>
                    <input type="date" name="destroy_date_0" id="destroy_date_0" />
                </div>
            </div>
        </div>
        <div class="row justify-content-center mt-3">
            <button type="button" id="add-box" class="rounded-circle" style="width: 40px; height: 40px;">+</button>
        </div>
        <div class="row justify-content-center mt-5 mb-5">
            <input type="submit" style="width: 100px; height: 40px;" />
        </div>
    </div>
</form>

@endsection
