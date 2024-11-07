import BoxList from './BoxList';
import '@testing-library/jest-dom';
import { render, screen, fireEvent, waitFor, act } from '@testing-library/react';
import React, { useState } from 'react';

describe(BoxList, () => {
    const INIT_BOX_ID = 4;
    const INIT_BOXES = [
        { id: 0, description: '', destroyDate: '' },
        { id: 1, description: 'Box 1', destroyDate: '2022-01-02' },
        { id: 2, description: 'Box 2', destroyDate: '2022-01-03' },
        { id: 3, description: 'Box 3', destroyDate: '2022-01-04' }
    ];

    function StatefulBoxList(props) {
        const [boxes, setBoxes] = useState(props.initBoxes);
        return <BoxList initNextBoxId={INIT_BOX_ID} boxes={boxes} setBoxes={setBoxes} />;
    }

    function renderBoxList(initBoxes) {
        render(<StatefulBoxList initBoxes={initBoxes} />);
    }

    describe('Initialization', () => {
        it('should not render any boxes if given an empty box list', () => {
            renderBoxList([]);

            expect(screen.queryByTestId('box_0')).toBeNull();
            expect(screen.queryByTestId('box_1')).toBeNull();
            expect(screen.queryByTestId('box_2')).toBeNull();
            expect(screen.queryByTestId('box_3')).toBeNull();
            expect(screen.queryByTestId('box_4')).toBeNull();
        })

        it('should render a single box if given a single box in its box list', () => {
            renderBoxList([INIT_BOXES[1]]);

            expect(screen.queryByTestId('box_0')).toBeNull();

            expect(screen.queryByTestId('box_1')).toBeInTheDocument();
            expect(screen.queryByTestId('description_1')).toHaveValue("Box 1");
            expect(screen.queryByTestId('destroy_date_1')).toHaveValue("2022-01-02");

            expect(screen.queryByTestId('box_2')).toBeNull();
            expect(screen.queryByTestId('box_3')).toBeNull();
            expect(screen.queryByTestId('box_4')).toBeNull();
        })

        it('should render all boxes in its given box list', () => {
            renderBoxList(INIT_BOXES);

            INIT_BOXES.forEach((box, i) => {
                expect(screen.queryByTestId(`box_${i}`)).toBeInTheDocument();
                expect(screen.queryByTestId(`description_${i}`)).toHaveValue(box.description);
                expect(screen.queryByTestId(`destroy_date_${i}`)).toHaveValue(box.destroyDate);
            });

            expect(screen.queryByTestId('box_4')).toBeNull();
        })
    })

    function addBox() {
        act(() => fireEvent.click(screen.getByTestId('add_box')));
    }

    function removeBox(id) {
        act(() => fireEvent.click(screen.getByTestId('remove_' + id)));
    }

    const MAX_BOX_ID = INIT_BOX_ID + 8;
    const INIT_BOX_IDS = INIT_BOXES.map(box => box.id);

    function expectBoxList(ids) {
        for (let id = 0; id <= MAX_BOX_ID; id++) {
            if (!ids.includes(id)) {
                expect(screen.queryByTestId('box_' + id)).toBeNull();
                continue;
            }

            expect(screen.queryByTestId('box_' + id)).toBeInTheDocument();
        }
    }

    describe('Adding and removing boxes', () => {
        it('should allow multiple boxes to be added when starting with none', () => {
            renderBoxList([]);

            addBox();
            expectBoxList([INIT_BOX_ID]);

            addBox();
            expectBoxList([INIT_BOX_ID, INIT_BOX_ID + 1]);

            addBox();
            expectBoxList([INIT_BOX_ID, INIT_BOX_ID + 1, INIT_BOX_ID + 2]);
        })

        it('should allow multiple boxes to be added when starting with one', () => {
            renderBoxList([INIT_BOXES[0]]);

            addBox();
            expectBoxList([INIT_BOX_IDS[0], INIT_BOX_ID]);

            addBox();
            expectBoxList([INIT_BOX_IDS[0], INIT_BOX_ID, INIT_BOX_ID + 1]);

            addBox();
            expectBoxList([INIT_BOX_IDS[0], INIT_BOX_ID, INIT_BOX_ID + 1, INIT_BOX_ID + 2]);
        })

        it('should allow multiple boxes to be added when starting with multiple', () => {
            renderBoxList(INIT_BOXES);

            addBox();
            expectBoxList([...INIT_BOX_IDS, INIT_BOX_ID]);

            addBox();
            expectBoxList([...INIT_BOX_IDS, INIT_BOX_ID, INIT_BOX_ID + 1]);

            addBox();
            expectBoxList([...INIT_BOX_IDS, INIT_BOX_ID, INIT_BOX_ID + 1, INIT_BOX_ID + 2]);
        })

        it('should allow a box to be removed when starting with two', () => {
            renderBoxList([INIT_BOXES[1], INIT_BOXES[0]]);

            expect(screen.queryByTestId('remove_1')).toBeNull();

            removeBox(0);
            expectBoxList([INIT_BOX_IDS[1]]);
        })

        it('should allow multiple boxes to be removed when starting with multiple', () => {
            renderBoxList(INIT_BOXES);

            expect(screen.queryByTestId('remove_0')).toBeNull();

            removeBox(1);
            expectBoxList([INIT_BOX_IDS[0], INIT_BOX_IDS[2], INIT_BOX_IDS[3]]);

            removeBox(3);
            expectBoxList([INIT_BOX_IDS[0], INIT_BOX_IDS[2]]);

            removeBox(2);
            expectBoxList([INIT_BOX_IDS[0]]);
        })

        it('should allow multiple boxes to be added and removed when starting with none', () => {
            renderBoxList([]);

            addBox();
            addBox();
            expectBoxList([INIT_BOX_ID, INIT_BOX_ID + 1]);

            removeBox(INIT_BOX_ID + 1);
            expectBoxList([INIT_BOX_ID]);

            addBox();
            addBox();
            addBox();
            expectBoxList([INIT_BOX_ID, INIT_BOX_ID + 2, INIT_BOX_ID + 3, INIT_BOX_ID + 4]);

            removeBox(INIT_BOX_ID + 3);
            expectBoxList([INIT_BOX_ID, INIT_BOX_ID + 2, INIT_BOX_ID + 4]);

            addBox();
            expectBoxList([INIT_BOX_ID, INIT_BOX_ID + 2, INIT_BOX_ID + 4, INIT_BOX_ID + 5]);

            removeBox(INIT_BOX_ID + 5);
            expectBoxList([INIT_BOX_ID, INIT_BOX_ID + 2, INIT_BOX_ID + 4]);

            addBox();
            addBox();
            expectBoxList([INIT_BOX_ID, INIT_BOX_ID + 2, INIT_BOX_ID + 4, INIT_BOX_ID + 6, INIT_BOX_ID + 7]);

            removeBox(INIT_BOX_ID + 6);
            expectBoxList([INIT_BOX_ID, INIT_BOX_ID + 2, INIT_BOX_ID + 4, INIT_BOX_ID + 7]);

            removeBox(INIT_BOX_ID + 7);
            expectBoxList([INIT_BOX_ID, INIT_BOX_ID + 2, INIT_BOX_ID + 4]);

            removeBox(INIT_BOX_ID + 2);
            removeBox(INIT_BOX_ID + 4);
            expectBoxList([INIT_BOX_ID]);
        })

        it('should allow multiple boxes to be added and removed when starting with one', () => {
            renderBoxList([{ id: 2, description: '', destroyDate: '' }]);

            addBox();
            expectBoxList([2, INIT_BOX_ID]);

            addBox();
            expectBoxList([2, INIT_BOX_ID, INIT_BOX_ID + 1]);

            removeBox(INIT_BOX_ID + 1);
            expectBoxList([2, INIT_BOX_ID]);

            addBox();
            addBox();
            addBox();
            expectBoxList([2, INIT_BOX_ID, INIT_BOX_ID + 2, INIT_BOX_ID + 3, INIT_BOX_ID + 4]);

            removeBox(INIT_BOX_ID + 3);
            expectBoxList([2, INIT_BOX_ID, INIT_BOX_ID + 2, INIT_BOX_ID + 4]);

            addBox();
            expectBoxList([2, INIT_BOX_ID, INIT_BOX_ID + 2, INIT_BOX_ID + 4, INIT_BOX_ID + 5]);

            removeBox(INIT_BOX_ID + 5);
            expectBoxList([2, INIT_BOX_ID, INIT_BOX_ID + 2, INIT_BOX_ID + 4]);

            addBox();
            addBox();
            expectBoxList([2, INIT_BOX_ID, INIT_BOX_ID + 2, INIT_BOX_ID + 4, INIT_BOX_ID + 6, INIT_BOX_ID + 7]);

            removeBox(INIT_BOX_ID + 6);
            expectBoxList([2, INIT_BOX_ID, INIT_BOX_ID + 2, INIT_BOX_ID + 4, INIT_BOX_ID + 7]);

            removeBox(INIT_BOX_ID);
            expectBoxList([2, INIT_BOX_ID + 2, INIT_BOX_ID + 4, INIT_BOX_ID + 7]);

            removeBox(INIT_BOX_ID + 2);
            removeBox(INIT_BOX_ID + 4);
            expectBoxList([2, INIT_BOX_ID + 7]);

            removeBox(INIT_BOX_ID + 7);
            expectBoxList([2]);

            addBox();
            expectBoxList([2, INIT_BOX_ID + 8]);

            removeBox(INIT_BOX_ID + 8);
            expectBoxList([2]);
        })

        it('should allow multiple boxes to be added and removed when starting with multiple', () => {
            renderBoxList(INIT_BOXES);

            addBox();
            addBox();
            expectBoxList([...INIT_BOX_IDS, INIT_BOX_ID, INIT_BOX_ID + 1]);

            removeBox(INIT_BOX_ID + 1);
            expectBoxList([...INIT_BOX_IDS, INIT_BOX_ID]);

            addBox();
            addBox();
            addBox();
            expectBoxList([...INIT_BOX_IDS, INIT_BOX_ID, INIT_BOX_ID + 2, INIT_BOX_ID + 3, INIT_BOX_ID + 4]);

            removeBox(INIT_BOX_ID + 3);
            expectBoxList([...INIT_BOX_IDS, INIT_BOX_ID, INIT_BOX_ID + 2, INIT_BOX_ID + 4]);

            addBox();
            expectBoxList([...INIT_BOX_IDS, INIT_BOX_ID, INIT_BOX_ID + 2, INIT_BOX_ID + 4, INIT_BOX_ID + 5]);

            removeBox(INIT_BOX_ID + 5);
            expectBoxList([...INIT_BOX_IDS, INIT_BOX_ID, INIT_BOX_ID + 2, INIT_BOX_ID + 4]);

            addBox();
            addBox();
            expectBoxList([...INIT_BOX_IDS, INIT_BOX_ID, INIT_BOX_ID + 2, INIT_BOX_ID + 4, INIT_BOX_ID + 6, INIT_BOX_ID + 7]);

            removeBox(INIT_BOX_ID + 6);
            expectBoxList([...INIT_BOX_IDS, INIT_BOX_ID, INIT_BOX_ID + 2, INIT_BOX_ID + 4, INIT_BOX_ID + 7]);

            removeBox(INIT_BOX_ID);
            expectBoxList([...INIT_BOX_IDS, INIT_BOX_ID + 2, INIT_BOX_ID + 4, INIT_BOX_ID + 7]);

            removeBox(INIT_BOX_ID + 2);
            removeBox(INIT_BOX_ID + 4);
            expectBoxList([...INIT_BOX_IDS, INIT_BOX_ID + 7]);

            removeBox(INIT_BOX_ID + 7);
            expectBoxList(INIT_BOX_IDS);

            addBox();
            expectBoxList([...INIT_BOX_IDS, INIT_BOX_ID + 8]);

            removeBox(INIT_BOX_ID + 8);
            expectBoxList(INIT_BOX_IDS);

            removeBox(INIT_BOX_IDS[1])
            expectBoxList([INIT_BOX_IDS[0], INIT_BOX_IDS[2], INIT_BOX_IDS[3]]);
        })
    })

    describe('Boxes have the correct states on adding and removing', () => {
        it('should not have content when a new box is added', () => {
            renderBoxList(INIT_BOXES);

            addBox();
            expect(screen.queryByTestId('description_4')).toHaveValue('');
            expect(screen.queryByTestId('destroy_date_4')).toHaveValue('');
        })

        it('should not change the content of the other boxes when new boxes are added', () => {
            renderBoxList(INIT_BOXES);

            addBox();
            expect(screen.queryByTestId('description_1')).toHaveValue('Box 1');
            expect(screen.queryByTestId('destroy_date_1')).toHaveValue('2022-01-02');

            expect(screen.queryByTestId('description_2')).toHaveValue('Box 2');
            expect(screen.queryByTestId('destroy_date_2')).toHaveValue('2022-01-03');

            expect(screen.queryByTestId('description_3')).toHaveValue('Box 3');
            expect(screen.queryByTestId('destroy_date_3')).toHaveValue('2022-01-04');
        })

        it('should not change the content of the other boxes when boxes are removed', () => {
            renderBoxList(INIT_BOXES);

            removeBox(1);
            expect(screen.queryByTestId('description_0')).toHaveValue('');
            expect(screen.queryByTestId('destroy_date_0')).toHaveValue('');

            expect(screen.queryByTestId('description_2')).toHaveValue('Box 2');
            expect(screen.queryByTestId('destroy_date_2')).toHaveValue('2022-01-03');

            expect(screen.queryByTestId('description_3')).toHaveValue('Box 3');
            expect(screen.queryByTestId('destroy_date_3')).toHaveValue('2022-01-04');
        })

        it('should not give boxes content when they are removed then added back', () => {
            renderBoxList(INIT_BOXES);

            removeBox(1);
            removeBox(2);
            removeBox(3);

            addBox();
            addBox();
            addBox();

            expect(screen.queryByTestId('description_4')).toHaveValue('');
            expect(screen.queryByTestId('destroy_date_4')).toHaveValue('');

            expect(screen.queryByTestId('description_5')).toHaveValue('');
            expect(screen.queryByTestId('destroy_date_5')).toHaveValue('');

            expect(screen.queryByTestId('description_6')).toHaveValue('');
            expect(screen.queryByTestId('destroy_date_6')).toHaveValue('');
        })

        it('should not take away the content of the first box when all others are removed', () => {
            renderBoxList([{ id: 8, description: "des", destroyDate: "2023-12-31" }, ...INIT_BOXES]);

            removeBox(0);
            removeBox(1);
            removeBox(2);
            removeBox(3);

            expect(screen.queryByTestId('description_8')).toHaveValue('des');
            expect(screen.queryByTestId('destroy_date_8')).toHaveValue('2023-12-31');
        })

        it('should not give the first box content when all others are removed then added back', () => {
            renderBoxList(INIT_BOXES);

            removeBox(1);
            removeBox(2);
            removeBox(3);

            addBox();
            addBox();
            addBox();

            expect(screen.queryByTestId('description_0')).toHaveValue('');
            expect(screen.queryByTestId('destroy_date_0')).toHaveValue('');
        })
    })

    describe('First box is irremovable', () => {
        it('should not render remove button for first box', () => {
            renderBoxList([INIT_BOXES[0]]);
            expect(screen.queryByTestId('remove_0')).toBeNull();
        })

        it('should render remove button for all boxes but the first', () => {
            renderBoxList(INIT_BOXES);
            expect(screen.queryByTestId('remove_0')).toBeNull();
            expect(screen.queryByTestId('remove_1')).toBeInTheDocument();
            expect(screen.queryByTestId('remove_2')).toBeInTheDocument();
            expect(screen.queryByTestId('remove_3')).toBeInTheDocument();
        })

        it('should render the remove button for all boxes but the first even as boxes are added and removed', () => {
            renderBoxList(INIT_BOXES);

            removeBox(1);
            expect(screen.queryByTestId('remove_0')).toBeNull();
            expect(screen.queryByTestId('remove_2')).toBeInTheDocument();
            expect(screen.queryByTestId('remove_3')).toBeInTheDocument();

            addBox();
            expect(screen.queryByTestId('remove_0')).toBeNull();
            expect(screen.queryByTestId('remove_2')).toBeInTheDocument();
            expect(screen.queryByTestId('remove_3')).toBeInTheDocument();
            expect(screen.queryByTestId('remove_4')).toBeInTheDocument();

            removeBox(2);
            expect(screen.queryByTestId('remove_0')).toBeNull();
            expect(screen.queryByTestId('remove_3')).toBeInTheDocument();
            expect(screen.queryByTestId('remove_4')).toBeInTheDocument();

            addBox();
            addBox();
            expect(screen.queryByTestId('remove_0')).toBeNull();
            expect(screen.queryByTestId('remove_3')).toBeInTheDocument();
            expect(screen.queryByTestId('remove_4')).toBeInTheDocument();
            expect(screen.queryByTestId('remove_5')).toBeInTheDocument();
            expect(screen.queryByTestId('remove_6')).toBeInTheDocument();

            removeBox(3);
            expect(screen.queryByTestId('remove_0')).toBeNull();
            expect(screen.queryByTestId('remove_4')).toBeInTheDocument();
            expect(screen.queryByTestId('remove_5')).toBeInTheDocument();
            expect(screen.queryByTestId('remove_6')).toBeInTheDocument();

            removeBox(4);
            expect(screen.queryByTestId('remove_0')).toBeNull();
            expect(screen.queryByTestId('remove_5')).toBeInTheDocument();
            expect(screen.queryByTestId('remove_6')).toBeInTheDocument();

            removeBox(5);
            expect(screen.queryByTestId('remove_0')).toBeNull();
            expect(screen.queryByTestId('remove_6')).toBeInTheDocument();

            removeBox(6);
            expect(screen.queryByTestId('remove_0')).toBeNull();
        })
    })
})
