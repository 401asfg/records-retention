// TODO: test

const PendingRetentionRequest = (props) => {
    return (
        <div className="border p-2 mt-2 mb-2">
            <div>{props.retentionRequest.requestor_name} - {props.retentionRequest.department_name}</div>
            <div>{props.retentionRequest.created_at}</div>
        </div>
    );
}

export default PendingRetentionRequest;
