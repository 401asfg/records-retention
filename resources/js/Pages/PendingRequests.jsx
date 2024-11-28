import Navbar from "../Components/Navbar";
import PendingRetentionRequest from "../Components/PendingRetentionRequest";

// TODO: test
// FIXME: pass requests through props?

const PendingRequests = () => {
    return (
        <div>
            <Navbar>Pending Requests</Navbar>
            <div className="container mt-3 mb-3">
                <div className="row">
                    {props.retentionRequests.forEach((retentionRequest) => {
                        <div className="col-md-4 col-12 justify-content-center">
                            <PendingRetentionRequest retentionRequest={retentionRequest} />
                        </div>
                    })}
                </div>
            </div>
        </div>
    );
}

export default PendingRequests;
