import BoxList from './BoxList';
import '@testing-library/jest-dom';
import { render, screen, fireEvent, waitFor, act } from '@testing-library/react';
import React from 'react';

describe(BoxList, () => {
    describe('Initialization', () => {
        const INIT_BOX_ID = 4;
        const boxes = [
            { id: 0, description: '', destroyDate: '' },
            { id: 1, description: 'Box 1', destroyDate: '2022-01-02' },
            { id: 2, description: 'Box 2', destroyDate: '2022-01-03' },
            { id: 3, description: 'Box 3', destroyDate: '2022-01-04' }
        ];

        it('should not render any boxes if given an empty box list', () => {
            render(<BoxList initNextBoxId={INIT_BOX_ID} boxes={[]} setBoxes={() => {}} />);
            expect(screen.queryByTestId('box_0')).toBeNull();
            expect(screen.queryByTestId('box_1')).toBeNull();
            expect(screen.queryByTestId('box_2')).toBeNull();
            expect(screen.queryByTestId('box_3')).toBeNull();
            expect(screen.queryByTestId('box_4')).toBeNull();
        })

        it('should render a single box if given a single box in its box list', () => {
            render(<BoxList initNextBoxId={INIT_BOX_ID} boxes={[boxes[1]]} setBoxes={() => {}} />);
            // TODO: implement
        })

        it('should render all boxes in its given box list', () => {

        })
    })

    describe('Adding and removing boxes', () => {

    })

    describe('Boxes have the correct states on adding and removing', () => {

    })

    describe('First box is irremovable', () => {
        it('should not render remove button for first box', () => {

        })

        it('should render remove button for second box but not the first', () => {

        })

        it('should still not be removable after adding and removing a box', () => {

        })
    })
})
