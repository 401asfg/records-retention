import Modal from './Modal';
import '@testing-library/jest-dom';
import { render, screen, fireEvent } from '@testing-library/react';
import React from 'react';

describe(Modal, () => {
    const onClose = jest.fn();

    function renderModal(isOpen) {
        render(
            <Modal isOpen={isOpen} onClose={onClose}>
                <div data-testid="modal-content">Modal Content</div>
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
            expect(screen.queryByTestId("modal-background")).toBeNull();
            expect(screen.queryByTestId("close-modal")).toBeNull();
            expect(screen.queryByTestId("modal-content")).toBeNull();
        });

        it("should render if isOpen is true", () => {
            renderModal(true);
            expect(screen.getByTestId("modal-background")).toBeInTheDocument();
            expect(screen.getByTestId("close-modal")).toBeInTheDocument();
            expect(screen.getByTestId("modal-content")).toBeInTheDocument();
        });
    })

    describe("Closing", () => {
        it("should call onClose when clicking the background", () => {
            renderModal(true);
            fireEvent.click(screen.getByTestId("modal-background"));
            expect(onClose).toHaveBeenCalledTimes(1);
        });

        it("should call onClose when clicking the close button", () => {
            renderModal(true);
            fireEvent.click(screen.getByTestId("close-modal"));
            expect(onClose).toHaveBeenCalledTimes(1);
        });
    })
});
