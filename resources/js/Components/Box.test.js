import Box from './Box';
import '@testing-library/jest-dom';
import { render, screen, fireEvent, waitFor, act } from '@testing-library/react';
import React from 'react';

describe(Box, () => {
    const BOX_ID = 3;

    function renderBox(description, destroyDate, setDescription, setDestroyDate, remove) {
        const box = {
            id: BOX_ID,
            description: description,
            destroyDate: destroyDate
        };

        render(<Box box={box} setDescription={setDescription} setDestroyDate={setDestroyDate} remove={remove} />);
    }

    function getDescription() {
        return screen.getByTestId('description-' + BOX_ID);
    }

    function queryDestroyDate() {
        return screen.queryByTestId("destroy-date-" + BOX_ID);
    }

    function getShred() {
        return screen.getByTestId("shred-" + BOX_ID);
    }

    function getPermanentStorage() {
        return screen.getByTestId("permanent-storage-" + BOX_ID);
    }

    function queryRemoveButton() {
        return screen.queryByTestId("remove-" + BOX_ID);
    }

    function expectBox(expectedDescription, expectedDestroyDate, isDestroyDatePresent, isShredChecked, isRemoveButtonPresent) {
        waitFor(() => {
            const description = getDescription();
            expect(description).toHaveValue(expectedDescription);

            const destroyDate = queryDestroyDate();

            if (isDestroyDatePresent) {
                expect(destroyDate).toBeInTheDocument();
                expect(destroyDate).toHaveValue(expectedDestroyDate);
            } else {
                expect(destroyDate).not.toBeInTheDocument();
            }

            const shred = getShred();
            const permanentStorage = getPermanentStorage();

            if (isShredChecked) {
                expect(shred).toBeChecked();
                expect(permanentStorage).not.toBeChecked();
            } else {
                expect(shred).not.toBeChecked();
                expect(permanentStorage).toBeChecked();
            }

            const removeButton = queryRemoveButton();

            if (isRemoveButtonPresent) {
                expect(removeButton).toBeInTheDocument();
            } else {
                expect(removeButton).not.toBeInTheDocument();
            }
        })
    }

    describe('Initialization', () => {
        const BOX_DESCRIPTION = "Test description";

        function renderBoxWithDefaults(destroyDate, remove) {
            renderBox(BOX_DESCRIPTION, destroyDate, () => {}, () => {}, remove);
        }

        function expectBoxWithDefaults(expectedDestroyDate, isRemoveButtonPresent) {
            expectBox(BOX_DESCRIPTION, expectedDestroyDate, true, true, isRemoveButtonPresent);
        }

        it('should have the given id, given description, an empty destroy date, a shred final disposition, and no remove button when given an empty destroy date and a null remove function', () => {
            renderBoxWithDefaults("", null);
            expectBoxWithDefaults("", false);
        });

        it('should still have an empty destroy date when given an invalid destroy date', () => {
            renderBoxWithDefaults("invalid-date", null);
            expectBoxWithDefaults("", false);
        });

        it('should have the given destroy date when given a valid destroy date', () => {
            const validDestroyDate = "2023-12-31";
            renderBoxWithDefaults(validDestroyDate, null);
            expectBoxWithDefaults(validDestroyDate, false);
        });

        it('should have a remove button when given a remove function', () => {
            renderBoxWithDefaults("", () => {});
            expectBoxWithDefaults("", true);
        });
    });

    describe('Final Disposition States', () => {
        const BOX_DESCRIPTION = "Test Description 2";
        const setDestroyDateMock = jest.fn();

        function renderBoxWithDefaults() {
            renderBox(BOX_DESCRIPTION, "", () => {}, setDestroyDateMock, () => {});
        }

        function expectBoxWithDefaults(expectedDestroyDate, isShred) {
            expectBox(BOX_DESCRIPTION, expectedDestroyDate, isShred, isShred, true);
        }

        beforeEach(() => {
            renderBoxWithDefaults();
            setDestroyDateMock.mockClear();
        })

        it('should allow the user to fill out the destroy date when the final disposition is set to shred', () => {
            const destroyDate = queryDestroyDate();
            act(() => {
                fireEvent.change(destroyDate, { target: { value: "2023-12-31" } });
            });

            expectBoxWithDefaults("2023-12-31", true);
            expect(setDestroyDateMock).toHaveBeenCalledWith("2023-12-31");
        })

        it('should remove the destroy date field on changing to permanent storage', () => {
            const permanentStorage = getPermanentStorage();
            act(() => {
                permanentStorage.click();
            })

            expectBoxWithDefaults("", false);
            expect(setDestroyDateMock).toHaveBeenCalledWith("");
        })

        it('should bring back an empty destroy date field on changing to back to shred', () => {
            const permanentStorage = getPermanentStorage();
            act(() => {
                permanentStorage.click();
            });

            expect(setDestroyDateMock).toHaveBeenCalledWith("");

            const shred = getShred();
            act(() => {
                shred.click();
            });

            expectBoxWithDefaults("", true);
        });

        it('should set a filled out destroy date to empty on changing to permanent storage and back to shred', () => {
            const destroyDate = queryDestroyDate();
            act(() => {
                fireEvent.change(destroyDate, { target: { value: "2023-12-31" } });
            });

            expect(setDestroyDateMock).toHaveBeenCalledWith("2023-12-31");

            const permanentStorage = getPermanentStorage();
            act(() => {
                permanentStorage.click();
            });

            expectBoxWithDefaults("", false);

            const shred = getShred();
            act(() => {
                shred.click();
            });

            expectBoxWithDefaults("", true);
        });

        it('should allow the user to fill out the destroy date again after toggling between permanent storage and shred', () => {
            const permanentStorage = getPermanentStorage();
            act(() => {
                permanentStorage.click();
            });

            const shred = getShred();
            act(() => {
                shred.click();
            });

            const destroyDate = queryDestroyDate();
            act(() => {
                fireEvent.change(destroyDate, { target: { value: "2023-12-31" } });
            });

            expectBoxWithDefaults("2023-12-31", true);
            expect(setDestroyDateMock).toHaveBeenCalledWith("2023-12-31");
        });

        it('should set the destroy date to empty again after filling it out for a second time on toggling between permanent storage and shred again', () => {
            const permanentStorage = getPermanentStorage();
            act(() => {
                permanentStorage.click();
            });

            const shred = getShred();
            act(() => {
                shred.click();
            });

            const destroyDate = queryDestroyDate();
            act(() => {
                fireEvent.change(destroyDate, { target: { value: "2023-12-31" } });
            });

            expect(setDestroyDateMock).toHaveBeenCalledWith("2023-12-31");

            act(() => {
                permanentStorage.click();
            });

            expectBoxWithDefaults("", false);

            act(() => {
                shred.click();
            });

            expectBoxWithDefaults("", true);
        });
    })

    describe('Description States', () => {
        it('should allow the user to fill out the description', () => {
            const setDescriptionMock = jest.fn();
            renderBox("", "", setDescriptionMock, () => {}, () => {});

            const description = getDescription();
            act(() => {
                fireEvent.change(description, { target: { value: "New Description" } });
            });

            expect(setDescriptionMock).toHaveBeenCalledWith("New Description");
        })

        it('should not be changed on switching from shred to permanent storage', () => {
            const setDescriptionMock = jest.fn();
            renderBox("Old Description", "", setDescriptionMock, () => {}, () => {});

            const permanentStorage = getPermanentStorage();
            act(() => {
                permanentStorage.click();
            });

            expect(setDescriptionMock).not.toHaveBeenCalled();
        })

        it('should not be changed on switching from permanent storage to shred', () => {
            const setDescriptionMock = jest.fn();
            renderBox("Old Description", "", setDescriptionMock, () => {}, () => {});

            const shred = getShred();
            act(() => {
                shred.click();
            });

            expect(setDescriptionMock).not.toHaveBeenCalled();
        })

        it('should not be changed on setting the destroy date', () => {
            const setDescriptionMock = jest.fn();
            renderBox("Old Description", "", setDescriptionMock, () => {}, () => {});

            const destroyDate = queryDestroyDate();
            act(() => {
                fireEvent.change(destroyDate, { target: { value: "2023-12-31" } });
            });

            expect(setDescriptionMock).not.toHaveBeenCalled();
        })
    })

    describe('Remove', () => {
        it('should call the remove function when the remove button is clicked', () => {
            const removeMock = jest.fn();
            renderBox("", "", () => {}, () => {}, removeMock);

            const removeButton = queryRemoveButton();
            act(() => {
                removeButton.click();
            });

            expect(removeMock).toHaveBeenCalled();
        })
    })
});
