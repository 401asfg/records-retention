import Box from "./Box";
import React, { useRef } from 'react';

// TODO: test adding and removing boxes, and those buttons being present, when initNextBoxId is undefined

const BoxList = (props) => {
    const nextBoxId = useRef(props.initNextBoxId);

    const addBox = () => {
        if (props.initNextBoxId === undefined) return;

        props.setBoxes([...props.boxes, {
            id: nextBoxId.current,
            description: "",
            destroyDate: ""
        }]);

        nextBoxId.current++;
    }

    const removeBox = (index) => {
        if (props.initNextBoxId === undefined) return;
        props.setBoxes([...props.boxes.slice(0, index), ...props.boxes.slice(index + 1)]);
    }

    const setBox = (index, box) => {
        const newBox = {
            id: props.boxes[index].id,
            description: box.description,
            destroyDate: box.destroyDate
        };

        props.setBoxes([...props.boxes.slice(0, index), newBox, ...props.boxes.slice(index + 1)]);
    }

    const setBoxDescription = (index, description) => {
        const newBox = {
            description: description,
            destroyDate: props.boxes[index].destroyDate
        };

        setBox(index, newBox);
    }

    const setBoxDestroyDate = (index, destroyDate) => {
        const newBox = {
            description: props.boxes[index].description,
            destroyDate: destroyDate
        };

        setBox(index, newBox);
    }

    return (
        <div>
            {props.boxes.map((box, i) =>
                // FIXME: refactor box/boxes into class?
                <Box
                    key={box.id}
                    box={box}
                    setDescription={(value) => setBoxDescription(i, value)}
                    setDestroyDate={(value) => setBoxDestroyDate(i, value)}
                    remove={(i === 0 || props.initNextBoxId === undefined) ? null : () => removeBox(i)}
                    isInitFinalDispositionBasedOnDestroyDate={
                        props.isInitFinalDispositionBasedOnDestroyDates === undefined
                        ? false
                        : props.isInitFinalDispositionBasedOnDestroyDates
                    }
                />
            )}
            {props.initNextBoxId !== undefined &&
                <div className="row justify-content-center">
                    <button
                        onClick={addBox}
                        type="button"
                        id="add-box"
                        className="rounded-circle"
                        style={{width: "40px", height: "40px"}}
                        data-testid="add-box"
                    >+</button>
                </div>
            }
        </div>
    );
}

export default BoxList;
