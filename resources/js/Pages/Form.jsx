import { useState, useRef } from "react";
import Box from "../Components/Box";
import SearchableDropdown from "../Components/SearchableDropdown";
import Modal from "../Components/Modal";
import axios from 'axios';

// TODO: test
// FIXME: remember previous, non-box related, inputs

const Form = () => {
    const INIT_BOX_ID = 1;
    const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    const [departmentId, setDepartmentId] = useState(null);
    const [managerName, setManagerName] = useState("");
    const [requestorName, setRequestorName] = useState("");
    const [requestorEmail, setRequestorEmail] = useState("");

    const nextBoxId = useRef(INIT_BOX_ID + 1);
    const [boxes, setBoxes] = useState([{
        id: INIT_BOX_ID,
        description: "",
        // FIXME: should this use null instead?
        destroyDate: ""
    }]);

    const [isInfoOpen, setIsInfoOpen] = useState(false);

    const addBox = () => {
        setBoxes([...boxes, {
            id: nextBoxId.current,
            description: "",
            // FIXME: should this use null instead?
            destroyDate: ""
        }]);

        nextBoxId.current++;
    }

    const removeBox = (index) => {
        setBoxes([...boxes.slice(0, index), ...boxes.slice(index + 1)]);
    }

    const setBox = (index, box) => {
        const newBox = {
            id: boxes[index].id,
            description: box.description,
            destroyDate: box.destroyDate
        };

        setBoxes([...boxes.slice(0, index), newBox, ...boxes.slice(index + 1)]);
    }

    const setBoxDescription = (index, description) => {
        const newBox = {
            description: description,
            destroyDate: boxes[index].destroyDate
        };

        setBox(index, newBox);
    }

    const setBoxDestroyDate = (index, destroyDate) => {
        const newBox = {
            description: boxes[index].description,
            destroyDate: destroyDate
        };

        setBox(index, newBox);
    }

    const handleSubmit = async (event) => {
        event.preventDefault();

        const data = {
            "retention_request": {
                "manager_name": managerName,
                "requestor_name": requestorName,
                "requestor_email": requestorEmail,
                "department_id": departmentId
            },
            "boxes": boxes.map((box) => { return {
                "description": box.description,
                "destroy_date": box.destroyDate
            }})
        }

        axios.defaults.headers.common['X-CSRF-TOKEN'] = CSRF_TOKEN;

        // FIXME: handle failure cases
        axios.post('api/retention-requests', data);
    };

    const Info = () => {
        return (
            <div>
                <div className="row text-center"><strong>Description Example</strong></div>
                <div>
                    Info<br/>
                    Info 2<br/>
                    Info 3
                </div>
            </div>
        );
    }

    return (
        <div className="position-relative">
            <h1 className="text-center">Records Retention Form</h1>
            {/* FIXME: replace with actual info */}
            <h4 className="text-center">Info Header</h4>

            <form onSubmit={handleSubmit} className="container mt-4">
                <div className="row">
                    <div className="col-sm-6 col-12 mt-3">
                        <label htmlFor="department" className="row"><strong>Department</strong></label>
                        <SearchableDropdown
                            sourceRoute="api/departments"
                            selectedOptionId={departmentId}
                            setSelectedOptionId={setDepartmentId}
                        />
                    </div>
                    <div className="col-sm-6 col-12 mt-3">
                        <label htmlFor="manager_name" className="row"><strong>Manager's Name</strong></label>
                        <input
                            type="text"
                            name="manager_name"
                            id="manager_name"
                            className="w-100"
                            value={managerName}
                            onChange={(event) => setManagerName(event.target.value)}
                            required
                        />
                    </div>
                </div>
                <div className="row">
                    <div className="col-sm-6 col-12 mt-3">
                        <label htmlFor="requestor_name" className="row"><strong>Completed By</strong></label>
                        <input
                            type="text"
                            name="requestor_name"
                            id="requestor_name"
                            className="w-100"
                            value={requestorName}
                            onChange={(event) => setRequestorName(event.target.value)}
                            required
                        />
                    </div>
                    <div className="col-sm-6 col-12 mt-3">
                        <label htmlFor="requestor_email" className="row"><strong>Email</strong></label>
                        <input
                            type="email"
                            name="requestor_email"
                            id="requestor_email"
                            className="w-100"
                            value={requestorEmail}
                            onChange={(event) => setRequestorEmail(event.target.value)}
                            required
                        />
                    </div>
                </div>
                <div className="row mt-5 justify-content-center">
                    <h3 className="text-center">Boxes</h3>
                    <div className="row">
                        <div className="col-lg-3 col-2">
                            <div style={{ position: "-webkit-sticky", position: "sticky", top: "20px" }}>
                                <div className="row justify-content-center">
                                    {/* TODO: replace info with actual info */}
                                    <div className="d-lg-block d-none">
                                        <div className="border bg-light p-2">
                                            <Info />
                                        </div>
                                    </div>
                                    <button
                                        className="d-lg-none d-block"
                                        onClick={() => setIsInfoOpen(true)}
                                        type="button"
                                        style={{width: "40px", height: "40px"}}
                                    >!</button>
                                </div>
                            </div>
                        </div>
                        <div className="col-lg-6 col-8">
                            {boxes.map((box, i) =>
                                // FIXME: refactor into class?
                                <Box
                                    key={box.id}
                                    box={box}
                                    setDescription={(value) => setBoxDescription(i, value)}
                                    setDestroyDate={(value) => setBoxDestroyDate(i, value)}
                                    remove={i === 0 ? null : () => removeBox(i)}
                                />
                            )}
                        </div>
                    </div>
                    <div className="row justify-content-center">
                        <button
                            onClick={addBox}
                            type="button"
                            id="add-box"
                            className="rounded-circle"
                            style={{width: "40px", height: "40px"}}
                        >+</button>
                    </div>
                    <div className="row justify-content-center mt-5 mb-5">
                        <input
                            type="submit"
                            style={{width: "100px", height: "40px"}}
                        />
                    </div>
                </div>
            </form>

            <Modal isOpen={isInfoOpen} onClose={() => setIsInfoOpen(false)}>
                <Info />
            </Modal>
        </div>
    );
}

export default Form;
