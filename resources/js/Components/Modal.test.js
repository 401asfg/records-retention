import Modal from './Modal';
import '@testing-library/jest-dom';
import { render, screen, fireEvent, waitFor, act } from '@testing-library/react';
import React from 'react';

describe(Modal, () => {
    const onClose = jest.fn();

    function renderModal(isOpen) {
        render(
            <Modal isOpen={isOpen} onClose={onClose}>
                <div data-testid="modal_content">Modal Content</div>
            </Modal>
        );
    }

    beforeAll(() => {
        const portal = document.createElement('div');
        portal.setAttribute('id', 'portal');
        document.body.appendChild(portal);
    })

    beforeEach(() => {
        onClose.mockClear();
    })

    describe("Initialization", () => {
        it("should not render if isOpen is false", () => {
            renderModal(false);
            expect(screen.queryByTestId("modal_background")).toBeNull();
            expect(screen.queryByTestId("close_modal")).toBeNull();
            expect(screen.queryByTestId("modal_content")).toBeNull();
        });

        it("should render if isOpen is true", () => {
            renderModal(true);
            expect(screen.getByTestId("modal_background")).toBeInTheDocument();
            expect(screen.getByTestId("close_modal")).toBeInTheDocument();
            expect(screen.getByTestId("modal_content")).toBeInTheDocument();
        });
    })

    describe("Closing", () => {
        it("should call onClose when clicking the background", () => {
            renderModal(true);
            fireEvent.click(screen.getByTestId("modal_background"));
            expect(onClose).toHaveBeenCalledTimes(1);
        });

        it("should call onClose when clicking the close button", () => {
            renderModal(true);
            fireEvent.click(screen.getByTestId("close_modal"));
            expect(onClose).toHaveBeenCalledTimes(1);
        });
    })
});
