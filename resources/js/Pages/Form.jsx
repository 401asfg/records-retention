import { useState, useRef } from "react";
import axios from 'axios';
import { useCookies } from 'react-cookie';

import Box from "../Components/Box";
import SearchableDropdown from "../Components/SearchableDropdown";
import Modal from "../Components/Modal";
import logo from "../../../public/logo.png";

// TODO: test
// TODO: test cookie system (make sure query is only used if there is a valid dept id and vice versa)
// TODO: change page title to retention record request form
// TODO: add logo to top of form
// TODO: add small version of logo as favicon
// TODO: add 404 page?
// FIXME: will the page be rerendered on changing the fields even without cookies having dependencies?
// FIXME: refactor styling?
// TODO: use content management system?
// FIXME: remember previous, non-box related, inputs

const Form = () => {
    const INIT_BOX_ID = 1;
    const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const COOKIE_OPTIONS = { maxAge: 60 * 60 * 24 * 14 };

    const [cookies, setCookie] = useCookies();

    const [departmentId, setDepartmentId] = useState(cookies.department_id || null);
    const [managerName, setManagerName] = useState(cookies.manager_name || "");
    const [requestorName, setRequestorName] = useState(cookies.requestor_name || "");
    const [requestorEmail, setRequestorEmail] = useState(cookies.requestor_email || "");

    const [isInfoOpen, setInfoOpen] = useState(false);
    const [isConfirmationOpen, setConfirmationOpen] = useState(false);
    const [isSubmissionSuccessfulOpen, setSubmissionSuccessfulOpen] = useState(false);

    const [isSubmissionFailedOpen, setSubmissionFailedOpen] = useState(false);
    const submissionFailedError = useRef("");

    const nextBoxId = useRef(INIT_BOX_ID + 1);
    const [boxes, setBoxes] = useState([{
        id: INIT_BOX_ID,
        description: "",
        // FIXME: should this use null instead?
        destroyDate: ""
    }]);

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

    const postRetentionRequest = () => {
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

        // TODO: test failure case
        axios.post('api/retention-requests', data)
            .then(() => { setSubmissionSuccessfulOpen(true) })
            .catch((error) => {
                submissionFailedError.current = error.response.data;
                setSubmissionFailedOpen(true);
            });
    }

    const confirm = () => {
        setConfirmationOpen(false);

        setCookie("manager_name", managerName, COOKIE_OPTIONS);
        setCookie("requestor_name", requestorName, COOKIE_OPTIONS);
        setCookie("requestor_email", requestorEmail, COOKIE_OPTIONS);
        setCookie("department_id", departmentId, COOKIE_OPTIONS);

        postRetentionRequest();
    }

    const submit = async (event) => {
        event.preventDefault();
        setConfirmationOpen(true);
    };

    const Info = () => {
        return (
            <div>
                <p className="text-center"><strong>Filling out Box Descriptions</strong></p>
                <p>Include the following in your Description of the box contents:</p>
                <ol>
                    <li>Brief description of the records.</li>
                    <li>Date ranges covered by the materials.</li>
                    <li>The relevant Record Classification Number (if possible) from the <a href="https://can01.safelinks.protection.outlook.com/?url=https%3A%2F%2Femployee.vcc.ca%2Fdepartments%2Foperational%2Frecords-management%2Frecords-retention-schedule%2Frecords-retention-schedule-rrs%2F&data=05%7C02%7Cmallan%40vcc.ca%7Cff4d15c23e4b488202e308dced51a45a%7C9d83cfc7633047d5b18d45bafe3b1d87%7C0%7C0%7C638646182108297053%7CUnknown%7CTWFpbGZsb3d8eyJWIjoiMC4wLjAwMDAiLCJQIjoiV2luMzIiLCJBTiI6Ik1haWwiLCJXVCI6Mn0%3D%7C0%7C%7C%7C&sdata=OzCNo0i5GxUUJnj6UiE4q4I5OFIFez0uqaqXPJZiud8%3D&reserved=0">Records Retention Schedule</a>. This will determine the eventual destruction date.</li>
                </ol>
                <p className="fst-italic">For Example:</p>
                <ul>
                    <li>Student exams from Certified Dental Assisting, Jan-April 2023 term, TE-400</li>
                    <li>Credit card receipts from DTN spa, Jan-Dec 2024, FI-40</li>
                </ul>
                <p>These details will determine the eventual destruction dates for this box. For these examples, the student exams must be kept for 2 years, so the destruction date will be May 2025; credit card receipts must be kept for 1 year, so the destruction date will be January 2025.</p>
            </div>
        );
    }

    return (
        <div className="container position-relative">
            <a href="https://library.vcc.ca/" className="row justify-content-center pt-2">
                <img src={logo} alt="Logo" style={{width: "200px", height: "auto"}}></img>
            </a>
            <h1 className="text-center mt-3 mb-5">Records Retention Form</h1>
            <div className="border border-dark p-2 mb-5">
                <p>Submit this form to authorize and send boxes of physical records to store at VCC's Downtown campus. Boxes will be held until the end of their retention period and then destroyed. Records should only be sent if they are no longer regularly consulted in office; boxes can be retrieved (up until destruction) with a few days notice.</p>
                <p>Consult the <a href="https://can01.safelinks.protection.outlook.com/?url=https%3A%2F%2Femployee.vcc.ca%2Fdepartments%2Foperational%2Frecords-management%2Frecords-retention-schedule%2Frecords-retention-schedule-rrs%2F&data=05%7C02%7Cmallan%40vcc.ca%7Cff4d15c23e4b488202e308dced51a45a%7C9d83cfc7633047d5b18d45bafe3b1d87%7C0%7C0%7C638646182108195445%7CUnknown%7CTWFpbGZsb3d8eyJWIjoiMC4wLjAwMDAiLCJQIjoiV2luMzIiLCJBTiI6Ik1haWwiLCJXVCI6Mn0%3D%7C0%7C%7C%7C&sdata=hVknz8R1Ol8kOR51MKdV4mITkfe6voY1ehnHEG%2Fb4uE%3D&reserved=0">Records Retention Schedule</a> to see when records may be destroyed. Please contact the <a href="https://can01.safelinks.protection.outlook.com/?url=https%3A%2F%2Femployee.vcc.ca%2Fdepartments%2Foperational%2Frecords-management%2Fcontact-records-management%2F&data=05%7C02%7Cmallan%40vcc.ca%7Cff4d15c23e4b488202e308dced51a45a%7C9d83cfc7633047d5b18d45bafe3b1d87%7C0%7C0%7C638646182108241374%7CUnknown%7CTWFpbGZsb3d8eyJWIjoiMC4wLjAwMDAiLCJQIjoiV2luMzIiLCJBTiI6Ik1haWwiLCJXVCI6Mn0%3D%7C0%7C%7C%7C&sdata=FTTGPn04zmxXnUD1rsNKdxJO5d3jexpr0QP8EBfzjnU%3D&reserved=0">Records Coordinator</a> if you have any questions.</p>
                <p>Complete the fields below; the form will be submitted for review and approval. Multiple boxes can be submitted for approval with one form.</p>
                <p>Once you receive an approved response, attach the form(s) to the box(es) of records and submit a <a href="https://can01.safelinks.protection.outlook.com/?url=https%3A%2F%2Ffsr.vcc.ca%2F&data=05%7C02%7Cmallan%40vcc.ca%7Cff4d15c23e4b488202e308dced51a45a%7C9d83cfc7633047d5b18d45bafe3b1d87%7C0%7C0%7C638646182108269967%7CUnknown%7CTWFpbGZsb3d8eyJWIjoiMC4wLjAwMDAiLCJQIjoiV2luMzIiLCJBTiI6Ik1haWwiLCJXVCI6Mn0%3D%7C0%7C%7C%7C&sdata=S99hSgPYXuGVofCb1hw0FFlIRv3TguyFzMhhK0VG7vc%3D&reserved=0">Facilities Service Request (FSR)</a> to have the boxes moved to Room 003 at DTN campus.</p>
            </div>
            <form onSubmit={submit} className="mt-4">
                <div className="row">
                    <div className="col-sm-6 col-12 mt-3">
                        <label htmlFor="department" className="row"><strong>Department</strong></label>
                        <SearchableDropdown
                            name="departments"
                            sourceRoute="api/departments"
                            selectedOptionId={departmentId}
                            setSelectedOptionId={setDepartmentId}
                            cookieOptions={COOKIE_OPTIONS}
                        />
                    </div>
                    <div className="col-sm-6 col-12 mt-3">
                        <label htmlFor="manager_name" className="row"><strong>Department Leader/Manager [Fullname]</strong></label>
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
                        <label htmlFor="requestor_name" className="row"><strong>Completed By [Fullname]</strong></label>
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
                                    <div className="d-lg-block d-none">
                                        <div className="border bg-light p-2">
                                            <Info />
                                        </div>
                                    </div>
                                    <button
                                        className="d-lg-none d-block"
                                        onClick={() => setInfoOpen(true)}
                                        type="button"
                                        style={{width: "40px", height: "40px"}}
                                    >!</button>
                                </div>
                            </div>
                        </div>
                        <div className="col-lg-6 col-8">
                            {boxes.map((box, i) =>
                                // FIXME: refactor box/boxes into class?
                                <Box
                                    key={box.id}
                                    box={box}
                                    setDescription={(value) => setBoxDescription(i, value)}
                                    setDestroyDate={(value) => setBoxDestroyDate(i, value)}
                                    remove={i === 0 ? null : () => removeBox(i)}
                                />
                            )}
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
                    </div>
                </div>
            </form>

            <Modal isOpen={isInfoOpen} onClose={() => setInfoOpen(false)}>
                <Info />
            </Modal>

            <Modal isOpen={isConfirmationOpen} onClose={() => setConfirmationOpen(false)}>
                <div className="container-fluid text-center">
                    <div className="row justify-content-center">Are you sure you're ready to submit this retention request?</div>
                    <div className="row justify-content-center mt-3">
                        <button type="button" onClick={confirm} style={{width: "100px"}}>Confirm</button>
                    </div>
                </div>
            </Modal>

            {/* TODO: have the page refresh on close? */}
            <Modal isOpen={isSubmissionSuccessfulOpen} onClose={() => setSubmissionSuccessfulOpen(false)}>
                <div className="row justify-content-center text-center">Your retention request was successfully submitted for approval!</div>
                <div className="row justify-content-center mt-3">
                    <button type="button" onClick={() => setSubmissionSuccessfulOpen(false)} style={{width: "100px"}}>Okay</button>
                </div>
            </Modal>

            <Modal isOpen={isSubmissionFailedOpen} onClose={() => setSubmissionFailedOpen(false)}>
                <div className="row justify-content-center text-center">The following error prevented your retention request from being submitted:</div>
                <div className="row justify-content-center text-center">{submissionFailedError.current}</div>
                <div className="row justify-content-center mt-3">
                    <button type="button" onClick={() => setSubmissionFailedOpen(false)} style={{width: "100px"}}>Okay</button>
                </div>
            </Modal>
        </div>
    );
}

export default Form;
