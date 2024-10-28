import "./bootstrap";
import "../css/app.css";

import ReactDOM from "react-dom/client";
import RequestForm from "./Pages/RequestForm";
import { BrowserRouter, Routes, Route } from 'react-router-dom';

// TODO: test router

const App = () => {
    return (
        <BrowserRouter>
            <Routes>
                <Route path="/" element={<RequestForm />} />
            </Routes>
        </BrowserRouter>
    );
}

ReactDOM.createRoot(document.getElementById("app")).render(<App />);
