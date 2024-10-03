import React from "react";

class Form extends React.Component {
    render() {
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
                        <div className="col-md-6 col-11">
                            <div className="row border p-2">
                                <div className="col-12">
                                    <div><strong>Description</strong></div>
                                    <textarea name="description_0" className="w-100" style={{height: "100px"}}></textarea>
                                </div>
                                <div className="col-lg-6 col-12 mt-1">
                                    <div><strong>Final Disposition</strong></div>
                                    <label htmlFor="shred_0"><input type="radio" name="final_disposition_0" id="shred_0"></input> Shred</label>
                                    <label htmlFor="permanant_storage_0"><input type="radio" name="final_disposition_0" id="permanant_storage_0" /> Permanant Storage</label>
                                </div>
                                <div className="col-lg-6 col-12 mt-1">
                                    <div className="row">
                                        <label htmlFor="destroy_date_0"><strong>Destroy Date</strong></label>
                                    </div>
                                    <input type="date" name="destroy_date_0" id="destroy_date_0" />
                                </div>
                            </div>
                        </div>
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
}

export default Form;
