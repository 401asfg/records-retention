import React from 'react';

const Box = () => {
    return (
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
    );
}

export default Box;
