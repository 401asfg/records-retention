import Box from './Box';
import '@testing-library/jest-dom';
import { render, screen } from '@testing-library/react';
import React from 'react';

describe(Box, () => {
    describe('Initialization', () => {
        const BOX_ID = 3;
        const BOX_DESCRIPTION = "Test description";

        function renderBox(destroyDate, remove) {
            const box = {
                id: BOX_ID,
                description: BOX_DESCRIPTION,
                destroyDate: destroyDate
            };

            render(<Box box={box} setDescription={() => {}} setDestroyDate={() => {}} remove={remove} />);
        }

        function expectBox(expectedDestroyDate, isRemoveButtonPresent) {
            const description = screen.getByTestId('description_' + BOX_ID);
            expect(description).toHaveValue(BOX_DESCRIPTION);

            const destroyDate = screen.getByTestId("destroy_date_" + BOX_ID);
            expect(destroyDate).toHaveValue(expectedDestroyDate);

            const finalDisposition = screen.getByTestId("shred_" + BOX_ID);
            expect(finalDisposition).toBeChecked();

            const removeButton = screen.queryByTestId("remove_" + BOX_ID);

            if (isRemoveButtonPresent) {
                expect(removeButton).toBeInTheDocument();
            } else {
                expect(removeButton).not.toBeInTheDocument();
            }
        }

        test('it should have the given id, given description, an empty destroy date, a shred final disposition, and no remove button when given an empty destroy date and a null remove function', () => {
            renderBox("", null);
            expectBox("", false);
        });

        test('it should still have an empty destroy date when given an invalid destroy date', () => {
            renderBox("invalid-date", null);
            expectBox("", false);
        });

        test('it should have the given destroy date when given a valid destroy date', () => {
            const validDestroyDate = "2023-12-31";
            renderBox(validDestroyDate, null);
            expectBox(validDestroyDate, false);
        });

        test('it should have a remove button when given a remove function', () => {
            renderBox("", () => {});
            expectBox("", true);
        });
    });
});
