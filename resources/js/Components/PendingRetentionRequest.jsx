import { useState } from "react";

// TODO: test
// FIXME: darker color when clicked?

const PendingRetentionRequest = (props) => {
    const [isHovered, setHovered] = useState(false);

    return (
        <a href={"retention-requests/" + props.retentionRequest.id} className="text-decoration-none text-black">
            <div
                className="border p-2 mt-2 mb-2"
                style={{backgroundColor: isHovered ? "#87CEEB" : ""}}
                onMouseEnter={() => setHovered(true)}
                onMouseLeave={() => setHovered(false)}
            >
                <div>{props.retentionRequest.requestor_name} - {props.retentionRequest.department_name}</div>
                <div>{props.retentionRequest.created_at}</div>
            </div>
        </a>
    );
}

export default PendingRetentionRequest;
