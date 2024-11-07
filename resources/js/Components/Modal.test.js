import Modal from './Modal';
import '@testing-library/jest-dom';
import { render, screen, fireEvent, waitFor, act } from '@testing-library/react';
import React from 'react';

describe(Modal, () => {
    function renderModal(isOpen) {
        render(
            <Modal isOpen={isOpen} onClose={() => {}}>
                <div data-testid="modal_content">Modal Content</div>
            </Modal>
        );
    }

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

    // TODO: write rest of tests
});
