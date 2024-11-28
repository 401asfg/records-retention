import "./bootstrap";
import "../css/app.css";

import ReactDOM from "react-dom/client";
import RequestForm from "./Pages/RequestForm";
import PendingRequests from "./Pages/PendingRequests";
import { BrowserRouter, Routes, Route } from 'react-router-dom';

// TODO: test router

const App = () => {
    const data = JSON.parse(document.getElementById("data").getAttribute("data"));

    return (
        <BrowserRouter>
            <Routes>
                <Route path="/" element={<RequestForm />} />
                <Route path="/retention-requests" element={<PendingRequests retentionRequests={data} />} />
            </Routes>
        </BrowserRouter>
    );
}

ReactDOM.createRoot(document.getElementById("app")).render(<App />);
