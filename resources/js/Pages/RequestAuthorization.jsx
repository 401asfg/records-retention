import BoxList from "../Components/BoxList";
import Navbar from "../Components/Navbar";
import Modal from "../Components/Modal";

import { useState, useRef, useEffect } from 'react';
import { useParams } from "react-router-dom";

// TODO: test

const RequestAuthorization = (props) => {
    const MAIL_FAILURE_RESPONSE_STATUS = 207;

    const MODAL_NONE = 0;
    const MODAL_SAVE_CONFIRMATION = 1;
    const MODAL_SAVE_FAILED = 2;
    const MODAL_AUTHORIZATION_CONFIRMATION = 3;
    const MODAL_AUTHORIZATION_SUCCESSFUL = 4;
    const MODAL_AUTHORIZATION_FAILED = 5;

    const { id } = useParams();

    const [openModal, setOpenModal] = useState(MODAL_NONE);
    const submissionError = useRef(null);

    const [boxes, setBoxes] = useState(props.data.boxes);

    useEffect(() => {
        document.title = "Request Authorization"
    }, []);

    const closeModal = () => {
        setOpenModal(MODAL_NONE);
    }

    const authorizeRetentionRequest = () => {
        const data = {
            "authorizing_user_id": 3,    // FIXME: THIS IS A TEMPORARY ID, pull the user id from the session
            "boxes": boxes.map((box) => {
                return {
                    "id": box.id,
                    "description": box.description,
                    "destroy_date": box.destroyDate
                }
            })
        };

        update(`${window.location.origin}/api/retention-requests/${id}/authorize`, data, MODAL_AUTHORIZATION_FAILED)
        .then((res) => {
            if (res.status === MAIL_FAILURE_RESPONSE_STATUS) submissionError.current = res.data;
            setOpenModal(MODAL_AUTHORIZATION_SUCCESSFUL);
        });
    }

    const updateRetentionRequest = () => {
        const data = {
            "boxes": boxes.map((box) => {
                return {
                    "id": box.id,
                    "description": box.description,
                    "destroy_date": box.destroyDate
                }
            })
        };

        update(`${window.location.origin}/api/retention-requests/${id}`, data, MODAL_SAVE_FAILED);
    }

    const update = async (url, data, failureModalCode) => {
        submissionError.current = null;

        // TODO: test failure case
        // FIXME: THIS FETCH CALL SENDS THE PUT REQUEST TO THE CURRENT PAGE, NOT THE PAGE SPECIFIED IN THE ROUTE
        return fetch(url, {
            method: 'PUT',
            headers: {
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        }).catch((error) => {
            console.log(error);
            submissionError.current = error.response.data;
            setOpenModal(failureModalCode);
        });
    }

    const back = () => {
        window.location.href = "/retention-requests";
    }

    const save = (event) => {
        event.preventDefault();

        closeModal();
        updateRetentionRequest();
        back();
    }

    const authorize = (event) => {
        event.preventDefault();

        closeModal();
        authorizeRetentionRequest();
    }

    return (
        <div>
            <Navbar>Request Authorization</Navbar>
            <div className="container mt-5 position-relative">
                <div className="row justify-content-center">
                    <div style={{maxWidth: "800px"}}>
                        <div className="row border">
                            <div className="col-6 text-start align-content-center"><strong>Completed By</strong></div>
                            <div className="col-6 text-end align-content-center">{props.data.retentionRequest.requestorName}</div>
                        </div>
                        <div className="row border">
                            <div className="col-6 text-start align-content-center"><strong>Email</strong></div>
                            <div className="col-6 text-end align-content-center">{props.data.retentionRequest.requestorEmail}</div>
                        </div>
                        <div className="row border">
                            <div className="col-6 text-start align-content-center"><strong>Department</strong></div>
                            <div className="col-6 text-end align-content-center">{props.data.retentionRequest.departmentName}</div>
                        </div>
                        <div className="row border">
                            <div className="col-6 text-start align-content-center"><strong>Department Leader/Manager</strong></div>
                            <div className="col-6 text-end align-content-center">{props.data.retentionRequest.managerName}</div>
                        </div>
                    </div>
                </div>
                <div className="mt-4">
                    <div className="row mt-5 justify-content-center">
                        <h3 className="text-center">Boxes</h3>
                        <div className="row justify-content-center">
                            <div className="col-lg-6 col-8">
                                <BoxList boxes={boxes} setBoxes={setBoxes} isInitFinalDispositionBasedOnDestroyDates={true} />
                                <div className="row justify-content-center mt-5 mb-5">
                                    <button
                                        onClick={back}
                                        style={{width: "100px", height: "40px"}}
                                    >Back</button>

                                    <button
                                        onClick={() => { setOpenModal(MODAL_SAVE_CONFIRMATION) }}
                                        className="ms-3 me-3"
                                        style={{width: "125px", height: "40px"}}
                                    >Finish Later</button>

                                    <button
                                        onClick={() => { setOpenModal(MODAL_AUTHORIZATION_CONFIRMATION) }}
                                        style={{width: "100px", height: "40px"}}
                                    >Authorize</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <Modal isOpen={openModal == MODAL_SAVE_CONFIRMATION} onClose={closeModal}>
                <form onSubmit={save} className="container-fluid text-center">
                    <div className="row justify-content-center">Are you sure you want to save the changes you've made?</div>
                    <div className="row justify-content-center mt-3">
                        <input type="submit" style={{ width: "100px" }} />
                    </div>
                </form>
            </Modal>

            <Modal isOpen={openModal == MODAL_AUTHORIZATION_CONFIRMATION} onClose={closeModal}>
                <form onSubmit={authorize} className="container-fluid text-center">
                    <div className="row justify-content-center">Are you sure you're ready to authorize this retention request?</div>
                    <div className="row justify-content-center mt-3">
                        <input type="submit" style={{ width: "100px" }} />
                    </div>
                </form>
            </Modal>

            {/* TODO: have the page refresh on close? */}
            <Modal isOpen={openModal == MODAL_AUTHORIZATION_SUCCESSFUL} onClose={back}>
                <div className="row justify-content-center text-center">The retention request was successfully authorized!</div>
                {submissionError.current && (
                    <div>
                        <div className="row justify-content-center text-center"><br />Unfortunately, the requestor could not be emailed due to the following error:</div>
                        <div className="row justify-content-center text-center">{submissionError.current}</div>
                        <div className="row justify-content-center text-center"><br />Please email them at: {props.data.retentionRequest.requestorEmail}</div>
                    </div>
                )}
                <div className="row justify-content-center mt-3">
                    <button type="button" onClick={back} style={{width: "100px"}}>Okay</button>
                </div>
            </Modal>

            <Modal isOpen={openModal == MODAL_AUTHORIZATION_FAILED} onClose={closeModal}>
                <div className="row justify-content-center text-center">The following error prevented the retention request from being authorized:</div>
                {submissionError.current && (
                    <div className="row justify-content-center text-center">{submissionError.current}</div>
                )}
                <div className="row justify-content-center mt-3">
                    <button type="button" onClick={closeModal} style={{width: "100px"}}>Okay</button>
                </div>
            </Modal>
        </div>
    );
}

export default RequestAuthorization;
