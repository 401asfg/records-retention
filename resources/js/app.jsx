import "./bootstrap";
import "../css/app.css";

import ReactDOM from "react-dom/client";
import RequestForm from "./Pages/RequestForm";
import PendingRequests from "./Pages/PendingRequests";
import RequestAuthorization from "./Pages/RequestAuthorization";
import { BrowserRouter, Routes, Route } from 'react-router-dom';
import axios from 'axios';

// TODO: test router

const App = () => {
    const data = JSON.parse(document.getElementById("data").getAttribute("data"));

    axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    return (
        <BrowserRouter>
            <Routes>
                <Route path="/" element={<RequestForm />} />
                <Route path="/retention-requests" element={<PendingRequests retentionRequests={data} />} />
                <Route path="/retention-requests/:id/edit" element={<RequestAuthorization data={data} />} />
            </Routes>
        </BrowserRouter>
    );
}

ReactDOM.createRoot(document.getElementById("app")).render(<App />);
