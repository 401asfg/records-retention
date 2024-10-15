import React from 'react';
import ReactDom from 'react-dom';

const Modal = (props) => {
    if (!props.isOpen) return null;

    return ReactDom.createPortal(
        <>
            <div onClick={props.onClose} className="position-fixed top-0 bottom-0 z-9" style={{background: "rgba(0, 0, 0, .7)", left: 0, right: 0}} />
            <div className="position-fixed top-50 translate-middle bg-light p-3 z-9" style={{left: "50%"}}>
                <div className='mb-4'>
                    {props.children}
                </div>
                <div className='row justify-content-center'>
                    <button type="button" onClick={props.onClose} style={{maxWidth: "100px"}}>Close</button>
                </div>
            </div>
        </>,
        document.getElementById('portal')
    );
}

export default Modal;
