import React from 'react';
import ReactDom from 'react-dom';

// TODO: test
// TODO: add appear and disappear animations?

const Modal = (props) => {
    if (!props.isOpen) return null;

    return ReactDom.createPortal(
        <>
            <div
                onClick={props.onClose}
                className="position-fixed top-0 bottom-0 z-9"
                style={{background: "rgba(0, 0, 0, .7)", left: 0, right: 0}}
                data-testid="modal-background"
            />
            <div className="col-md-6 col-10 position-fixed top-50 translate-middle bg-light z-9" style={{left: "50%", minHeight: "100px"}}>
                <div className='row justify-content-end m-0' style={{background: 'rgba(215, 215, 215, 1)'}}>
                    <button
                        type="button"
                        onClick={props.onClose}
                        style={{border: "none", background: "unset", width: "30px", height: "30px"}}
                        data-testid="close-modal"
                    >X</button>
                </div>
                <div className='m-3'>
                    {props.children}
                </div>
            </div>
        </>,
        document.getElementById('portal')
    );
}

export default Modal;
