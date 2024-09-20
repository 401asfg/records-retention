@extends('layout')

@section('title', 'Requestor Form')

@section('content')

<h1 class="text-center">Requestor Form</h1>
<h4 class="text-center">Info Header</h4>

<form action="retention-requests" method="post" class="container mt-5">
    <div class="row">
        <div class="col-6">
            <label for="department_name" class="row">Department Name</label>
            <input type="text" name="department_name" id="department_name" class="row w-100" />
        </div>
        <div class="col-6">
            <label for="manager_name" class="row">Manager's Name</label>
            <input type="text" name="manager_name" id="manager_name" class="row w-100" />
        </div>
    </div>
    <div class="row mt-3">
        <div class="col-6">
            <label for="requestor_name" class="row">Completed By</label>
            <input type="text" name="requestor_name" id="requestor_name" class="row w-100" />
        </div>
        <div class="col-6">
            <label for="requestor_email" class="row">Email</label>
            <input type="text" name="requestor_email" id="requestor_email" class="row w-100" />
        </div>
    </div>
    <div class="row mt-5 justify-content-center">
        <h3 class="text-center">Boxes</h3>
        <div class="col-6">
            <div class="row border">
                <div class="col-12">
                    <div>Description</div>
                    <textarea name="description_0" class="w-100" style="height: 100px;"></textarea>
                </div>
                <div class="col-12">
                    <div>Final Disposition</div>
                    <label for="shred_0"><input type="radio" name="final_disposition_0" id="shred_0"> Shred</label>
                    <label for="permanant_storage_0"><input type="radio" name="final_disposition_0" id="permanant_storage_0" /> Permanant Storage</label>
                </div>
                <div class="col-12">
                    <label for="destroy_date_0" class="row">Destroy Date</label>
                    <input type="date" name="destroy_date_0" id="destroy_date_0" />
                </div>
            </div>
        </div>
        <div class="row justify-content-center mt-5">
            <button type="button" id="add-box" class="rounded-circle" style="width: 40px; height: 40px;">+</button>
        </div>
    </div>
</form>

@endsection
