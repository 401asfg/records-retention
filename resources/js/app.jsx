import "./bootstrap";
import "../css/app.css";

import ReactDOM from "react-dom/client";
import Form from "./Pages/Form";
import { BrowserRouter, Routes, Route } from 'react-router-dom';

const App = () => {
    return (
        <BrowserRouter>
            <Routes>
                <Route path="/" element={<Form />} />
            </Routes>
        </BrowserRouter>
    );
}

ReactDOM.createRoot(document.getElementById("app")).render(<App />);
