import React from "react";
import Box from "../Components/Box";

const Form = () => {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    const handleSubmit = async (e) => {
        e.preventDefault();

        await fetch('/retention-requests', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify(formData),
        });
    };

    return (
        <div>
            <h1 className="text-center">Records Retention Form</h1>
            <h4 className="text-center">Info Header</h4>

            <form onSubmit={handleSubmit} className="container mt-4">
                <div className="row">
                    <div className="col-sm-6 col-12 mt-3">
                        <label htmlFor="department_name" className="row"><strong>Department Name</strong></label>
                        <input type="text" name="department_name" id="department_name" className="w-100" />
                    </div>
                    <div className="col-sm-6 col-12 mt-3">
                        <label htmlFor="manager_name" className="row"><strong>Manager's Name</strong></label>
                        <input type="text" name="manager_name" id="manager_name" className="w-100" />
                    </div>
                </div>
                <div className="row">
                    <div className="col-sm-6 col-12 mt-3">
                        <label htmlFor="requestor_name" className="row"><strong>Completed By</strong></label>
                        <input type="text" name="requestor_name" id="requestor_name" className="w-100" />
                    </div>
                    <div className="col-sm-6 col-12 mt-3">
                        <label htmlFor="requestor_email" className="row"><strong>Email</strong></label>
                        <input type="email" name="requestor_email" id="requestor_email" className="w-100" />
                    </div>
                </div>
                <div className="row mt-5 justify-content-center">
                    <h3 className="text-center">Boxes</h3>
                    <Box />
                    <div className="row justify-content-center mt-3">
                        <button type="button" id="add-box" className="rounded-circle" style={{width: "40px", height: "40px"}}>+</button>
                    </div>
                    <div className="row justify-content-center mt-5 mb-5">
                        <input type="submit" style={{width: "100px", height: "40px"}} />
                    </div>
                </div>
            </form>
        </div>
    );
}

export default Form;
