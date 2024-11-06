import React, { useState } from 'react';

// TODO: test
// TODO: add fade in and fade out when box is added and removed respectively

const Box = (props) => {
    const FINAL_DISPOSITION_SHRED = "shred";
    const FINAL_DISPOSITION_PERMANENT_STORAGE = "permanent_storage";

    const [finalDisposition, setFinalDisposition] = useState(FINAL_DISPOSITION_SHRED);

    const onFinalDispositionChange = (event) => {
        setFinalDisposition(event.target.value);
        if (event.target.value !== FINAL_DISPOSITION_SHRED) props.setDestroyDate("");
    }

    return (
        <div className="row d-flex justify-content-center align-items-center">
            <div className="position-relative">
                <div className="row border p-2 mb-5">
                    <div className="col-12">
                        <div><strong>Description</strong></div>
                        <textarea
                            name={"description_" + props.box.id}
                            className="w-100"
                            style={{height: "100px"}}
                            value={props.box.description}
                            onChange={(event) => props.setDescription(event.target.value)}
                            data-testid={"description_" + props.box.id}
                            required
                        />
                    </div>
                    <div className="col-lg-6 col-12 mt-1 mb-2">
                        <div><strong>Final Disposition</strong></div>
                        <label htmlFor={"shred_" + props.box.id} style={{marginRight: "10px"}}>
                            <input
                                type="radio"
                                name={"final_disposition_" + props.box.id}
                                style={{marginRight: "5px"}}
                                id={"shred_" + props.box.id}
                                value={FINAL_DISPOSITION_SHRED}
                                checked={finalDisposition === FINAL_DISPOSITION_SHRED}
                                onChange={onFinalDispositionChange}
                                data-testid={"shred_" + props.box.id}
                                required
                            /> Shred
                        </label>
                        <label htmlFor={"permanent_storage_" + props.box.id} style={{marginRight: "10px"}}>
                            <input
                                type="radio"
                                name={"final_disposition_" + props.box.id}
                                style={{marginRight: "5px"}}
                                id={"permanent_storage_" + props.box.id}
                                value={FINAL_DISPOSITION_PERMANENT_STORAGE}
                                checked={finalDisposition === FINAL_DISPOSITION_PERMANENT_STORAGE}
                                onChange={onFinalDispositionChange}
                                data-testid={"permanent_storage_" + props.box.id}
                                required
                            /> Permanent Storage
                        </label>
                    </div>
                    {(finalDisposition === FINAL_DISPOSITION_SHRED) && (
                        <div className="col-lg-6 col-12 mt-1">
                            <div className="row">
                                <label htmlFor={"destroy_date_" + props.box.id}><strong>Destroy Date</strong></label>
                            </div>
                            <input
                                type="date"
                                name={"destroy_date_" + props.box.id}
                                id={"destroy_date_" + props.box.id}
                                value={props.box.destroyDate}
                                onChange={(event) => props.setDestroyDate(event.target.value)}
                                data-testid={"destroy_date_" + props.box.id}
                                required
                            />
                        </div>
                    )}
                </div>
                {(props.remove !== null) && (
                    <div className="position-absolute" style={{top: "0", right: "-60px"}}>
                        <button
                            onClick={props.remove}
                            type="button"
                            id="remove-box"
                            className="rounded-circle"
                            style={{width: "40px", height: "40px"}}
                            data-testid={"remove_" + props.box.id}
                        >-</button>
                    </div>
                )}
            </div>
        </div>
    );
}

export default Box;
